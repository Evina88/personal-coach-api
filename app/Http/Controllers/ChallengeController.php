<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Services\GitHubService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChallengeController extends Controller
{
    public function __construct(protected GitHubService $github) {}

    // POST /api/challenges/sync
    public function sync(Request $request)
    {
        $owner = env('GITHUB_OWNER', 'Evina88');
        $repo  = env('GITHUB_REPO', 'coding-challenges');

        $files = $this->github->fetchChallenges($owner, $repo);

        if (!is_array($files) || empty($files)) {
            return response()->json([
                'message' => 'No files found in the GitHub repo (or the repo is empty).',
                'imported' => 0
            ]);
        }

        $imported = 0;

        foreach ($files as $file) {
            if (($file['type'] ?? '') !== 'file') continue;

            $name = $file['name'] ?? '';
            // Parse difficulty from filename, e.g. "01-easy-two-sum.md"
            preg_match('/(easy|medium|hard)/i', $name, $m);
            $difficulty = isset($m[1]) ? strtolower($m[1]) : null;

            // Derive a nice title and slug
            $title = trim(preg_replace('/^\d+\-?(easy|medium|hard)?\-?/i', '', $name));
            $title = preg_replace('/\.md$/i', '', $title);
            $title = str_replace('-', ' ', $title);
            $title = ucwords($title);
            $slug  = Str::slug($title);

            Challenge::updateOrCreate(
                ['slug' => $slug],
                [
                    'title'       => $title,
                    'difficulty'  => $difficulty,
                    'github_path' => $file['path'] ?? null,
                    'github_url'  => $file['html_url'] ?? null,
                ]
            );

            $imported++;
        }

        return response()->json([
            'message'  => "Sync complete.",
            'imported' => $imported,
        ]);
    }

    // GET /api/challenges
    public function index()
    {
        return Challenge::orderBy('difficulty')->orderBy('title')->get();
    }

    // POST /api/challenges/{id}/complete
    public function complete(Request $request, int $id)
    {
        $challenge = Challenge::findOrFail($id);
        $request->user()->challenges()->syncWithoutDetaching([
            $challenge->id => ['completed' => true],
        ]);

        return response()->json(['message' => 'Challenge marked as completed.']);
    }

    // GET /api/challenges/suggest
    public function suggest(Request $request)
    {
        $user = $request->user();
        $completedCount = $user->challenges()->wherePivot('completed', true)->count();

        $targetDifficulty = match (true) {
            $completedCount < 3 => 'easy',
            $completedCount < 7 => 'medium',
            default              => 'hard',
        };

        $suggestion = Challenge::whereDoesntHave('users', function ($q) use ($user) {
                $q->where('user_id', $user->id)->where('completed', true);
            })
            ->when($targetDifficulty, fn($q) => $q->where('difficulty', $targetDifficulty))
            ->orderBy('title')
            ->first();

        if (!$suggestion) {
            $suggestion = Challenge::whereDoesntHave('users', function ($q) use ($user) {
                    $q->where('user_id', $user->id)->where('completed', true);
                })
                ->orderBy('difficulty')
                ->orderBy('title')
                ->first();
        }

        if (!$suggestion) {
            return response()->json(['message' => 'All challenges completed. 🎉']);
        }

        return response()->json([
            'target_difficulty' => $targetDifficulty,
            'challenge'         => $suggestion,
        ]);
    }
}
