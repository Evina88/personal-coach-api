<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GitHubService
{
    protected $baseUrl = 'https://api.github.com/repos';

    public function fetchChallenges($owner, $repo, $path = '')
    {
        $url = "{$this->baseUrl}/{$owner}/{$repo}/contents/{$path}";

        $headers = [];
        if (config('services.github.token')) {
            $headers['Authorization'] = 'Bearer ' . config('services.github.token');
        }

        $response = Http::withHeaders($headers)->get($url);

        return $response->successful() ? $response->json() : [];
    }
}
