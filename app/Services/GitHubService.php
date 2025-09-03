<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GitHubService
{
    protected string $api = 'https://api.github.com';

    /**
     * Shared HTTP client with required headers.
     */
    protected function client()
    {
        $headers = [
            'Accept'     => 'application/vnd.github+json',
            'User-Agent' => 'personal-coach-api',
        ];

        if ($t = config('services.github.token')) {
            $headers['Authorization'] = "Bearer {$t}";
        }

        return Http::withHeaders($headers);
    }

    /**
     * Search for repositories likely containing coding challenges for a given language.
     * Returns the raw repo items from GitHub's search API.
     */
    public function searchChallengeRepos(string $language, int $limit = 10): array
    {
        // Stars filter avoids very low-signal repos; tweak as needed
        $q = sprintf('(kata OR challenge OR exercises) language:%s stars:>5', $language);

        $resp = $this->client()->get("{$this->api}/search/repositories", [
            'q'        => $q,
            'sort'     => 'stars',
            'order'    => 'desc',
            'per_page' => $limit,
        ]);

        return $resp->successful() ? ($resp->json('items') ?? []) : [];
    }

    /**
     * Scan common directories in a repo and collect Markdown files (one level deep).
     */
    public function listChallengeFiles(string $owner, string $repo, int $perRepoLimit = 10): array
    {
        $roots = ['', 'katas', 'kata', 'exercises', 'exercise', 'problems', 'challenges', 'tasks', 'practice'];
        $files = [];

        foreach ($roots as $root) {
            // simple breadth-first up to one subdir level
            $queue = [ltrim($root, '/')];
            $visited = 0;

            while (!empty($queue) && count($files) < $perRepoLimit && $visited < 50) {
                $path = array_shift($queue);
                $url  = rtrim("{$this->api}/repos/{$owner}/{$repo}/contents/{$path}", '/');

                $resp = $this->client()->get($url);
                $visited++;

                if (!$resp->successful()) {
                    continue;
                }

                $entries = $resp->json();
                if (!is_array($entries)) {
                    continue;
                }

                foreach ($entries as $entry) {
                    $type = $entry['type'] ?? '';
                    $name = strtolower($entry['name'] ?? '');

                    if ($type === 'file' && str_ends_with($name, '.md')) {
                        $files[] = $entry;
                        if (count($files) >= $perRepoLimit) {
                            break;
                        }
                    }

                    if ($type === 'dir') {
                        $queue[] = ltrim(($entry['path'] ?? ''), '/');
                    }
                }
            }

            if (count($files) >= $perRepoLimit) {
                break;
            }
        }

        return $files;
    }

    /**
     * Best-effort difficulty guess from filename or path.
     */
    protected function inferDifficultyFromName(string $filenameOrPath): ?string
    {
        $low = strtolower($filenameOrPath);
        if (str_contains($low, 'easy')) return 'easy';
        if (str_contains($low, 'medium') || str_contains($low, 'intermediate')) return 'medium';
        if (str_contains($low, 'hard') || str_contains($low, 'advanced')) return 'hard';
        return null;
    }

    /**
     * High-level helper: for a language, return a normalized list of challenge items
     * discovered across multiple popular repos.
     */
    public function findChallengesByLanguage(string $language, int $repoLimit = 3, int $filesPerRepo = 5): array
    {
        $repos = $this->searchChallengeRepos($language, $repoLimit);
        $out   = [];

        foreach ($repos as $r) {
            $owner = $r['owner']['login'] ?? null;
            $name  = $r['name'] ?? null;
            if (!$owner || !$name) {
                continue;
            }

            $files = $this->listChallengeFiles($owner, $name, $filesPerRepo);

            foreach ($files as $f) {
                $rawName = (string)($f['name'] ?? '');
                $path    = (string)($f['path'] ?? '');

                $title = preg_replace('/\.md$/i', '', $rawName);
                $title = ucwords(str_replace(['-', '_'], ' ', $title));

                $out[] = [
                    'title'       => $title,
                    'repo'        => "{$owner}/{$name}",
                    'github_url'  => $f['html_url'] ?? null,
                    'path'        => $path,
                    'difficulty'  => $this->inferDifficultyFromName($rawName . ' ' . $path), // may be null
                    'language'    => $language,
                ];
            }
        }

        return $out;
    }
}
