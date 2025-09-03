<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GitHubService;

class ChallengeController extends Controller
{
    public function __construct(protected GitHubService $github) {}

    /**
     * GET /api/challenges/search?lang=php&level=easy&limit=5
     * Live GitHub search: finds challenge markdown files across repos.
     */
    public function search(Request $request)
    {
        $request->validate([
            'lang'  => 'required|string|max:40',
            'level' => 'nullable|in:easy,medium,hard',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $lang  = strtolower($request->query('lang'));
        $level = $request->query('level');
        $limit = (int) $request->query('limit', 5);

        // Pull a pool of candidates from GitHub (broader pool helps filtering)
        $pool = $this->github->findChallengesByLanguage($lang, repoLimit: 5, filesPerRepo: 8);

        // If a level is requested, filter by inferred difficulty.
        // If that yields nothing, gracefully fall back to the unfiltered pool.
        if ($level) {
            $filtered = array_values(array_filter(
                $pool,
                fn ($c) => ($c['difficulty'] ?? null) === $level
            ));
            if (!empty($filtered)) {
                $pool = $filtered;
            }
        }

        return response()->json(array_slice($pool, 0, $limit));
    }

    /**
     * GET /api/challenges/suggest
     * Suggest a single challenge based on user's languages and history.
     * - language: first from ?lang, else user's languages_learning, else 'php'
     * - level:    derived from user's completed count (if you track it), else defaults 'easy'
     */
    public function suggest(Request $request)
    {
        $user = $request->user();

        // Language preference: explicit ?lang, else first in user's languages_learning, else 'php'
        $explicitLang = $request->query('lang');
        if ($explicitLang) {
            $lang = strtolower(trim($explicitLang));
        } elseif ($user && $user->languages_learning) {
            $langs = array_values(array_filter(array_map('trim', explode(',', $user->languages_learning))));
            $lang  = strtolower($langs[0] ?? 'php');
        } else {
            $lang = 'php';
        }

        // Completed count if you already track it via pivot; otherwise default to 0
        $completedCount = 0;
        if ($user && method_exists($user, 'challenges')) {
            $completedCount = (int) $user->challenges()->wherePivot('completed', true)->count();
        }

        // Target difficulty based on simple progression heuristic
        $targetLevel = match (true) {
            $completedCount < 3 => 'easy',
            $completedCount < 7 => 'medium',
            default             => 'hard',
        };

        // Fetch candidates from GitHub and try to match target level; fall back if none
        $pool = $this->github->findChallengesByLanguage($lang, repoLimit: 5, filesPerRepo: 8);

        $candidates = array_values(array_filter(
            $pool,
            fn ($c) => ($c['difficulty'] ?? null) === $targetLevel
        ));
        if (empty($candidates)) {
            $candidates = $pool; // fall back to any difficulty
        }

        if (empty($candidates)) {
            return response()->json([
                'language' => $lang,
                'target_level' => $targetLevel,
                'message' => 'No challenges found on GitHub for this language.'
            ]);
        }

        // For now, return the first candidate; we can randomize/diversify later
        return response()->json([
            'language'       => $lang,
            'target_level'   => $targetLevel,
            'completed_count'=> $completedCount,
            'challenge'      => $candidates[0],
        ]);
    }
}
