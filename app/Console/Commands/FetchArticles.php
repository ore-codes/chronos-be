<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

final class FetchArticles extends Command
{
    protected $signature = 'articles:fetch {source=all : Source to fetch articles from (all, news-api, guardian, ny-times)}';
    protected $description = 'Fetch articles from external APIs and store them in the database';

    public function handle(): void
    {
        $source = $this->argument('source');
        $this->info("Fetching articles from source: {$source}");

        $sources = [
            'news-api' => 'fetchFromNewsAPI',
            'guardian' => 'fetchFromGuardian',
            'ny-times' => 'fetchFromNYTimes',
        ];

        if ('all' === $source) {
            foreach ($sources as $method) {
                $this->{$method}();
            }
        } elseif (array_key_exists($source, $sources)) {
            $this->{$sources[$source]}();
        } else {
            $this->error('Invalid source provided. Allowed values: all, news-api, guardian, ny-times');
            return;
        }

        $this->info('Articles fetched and stored successfully!');
    }

    private function fetchFromSource(string $cacheKey, string $url, array $params, callable $mapResponse): void
    {
        $response = Cache::remember($cacheKey, 3600, fn() => Http::get($url, $params)->json());

        if ( ! $response || empty($response)) {
            $this->error("Failed to fetch articles from {$cacheKey}.");
            return;
        }

        $articles = $mapResponse($response);

        foreach ($articles as $article) {
            Article::updateOrCreate(
                ['title' => $article['title']],
                [
                    'content' => $article['content'],
                    'author' => $article['author'],
                    'source' => $article['source'],
                    'category' => $article['category'],
                    'published_at' => $article['published_at'],
                ],
            );
        }

        $this->info("Fetched articles from {$cacheKey}.");
    }

    private function fetchFromNewsAPI(): void
    {
        $this->fetchFromSource(
            'articles:newsapi',
            'https://newsapi.org/v2/top-headlines',
            [
                'apiKey' => env('NEWS_API_KEY'),
                'language' => 'en',
                'pageSize' => 30,
            ],
            function ($response) {
                return collect($response['articles'] ?? [])->map(function ($article) {
                    return [
                        'title' => $article['title'],
                        'content' => $article['content'] ?? $article['description'] ?? 'No content',
                        'author' => $article['author'],
                        'source' => $article['source']['name'],
                        'category' => 'General',
                        'published_at' => $article['publishedAt'],
                    ];
                })->toArray();
            },
        );
    }

    private function fetchFromGuardian(): void
    {
        $this->fetchFromSource(
            'articles:guardian',
            'https://content.guardianapis.com/search',
            [
                'api-key' => env('GUARDIAN_API_KEY'),
                'show-fields' => 'headline,byline,bodyText',
                'page-size' => 30,
            ],
            function ($response) {
                return collect($response['response']['results'] ?? [])->map(function ($article) {
                    return [
                        'title' => $article['webTitle'],
                        'content' => $article['fields']['bodyText'] ?? 'No content',
                        'author' => $this->removeByFrom($article['fields']['byline'] ?? null),
                        'source' => 'The Guardian',
                        'category' => $article['sectionName'] ?? 'General',
                        'published_at' => $article['webPublicationDate'],
                    ];
                })->toArray();
            },
        );
    }

    private function fetchFromNYTimes(): void
    {
        $this->fetchFromSource(
            'articles:nyt',
            'https://api.nytimes.com/svc/topstories/v2/home.json',
            [
                'api-key' => env('NYT_API_KEY'),
            ],
            function ($response) {
                return collect($response['results'] ?? [])->map(function ($article) {
                    return [
                        'title' => $article['title'],
                        'content' => $article['abstract'] ?? 'No content',
                        'author' => $this->removeByFrom($article['byline'] ?? null),
                        'source' => 'New York Times',
                        'category' => $article['section'] ?? 'General',
                        'published_at' => $article['published_date'],
                    ];
                })->toArray();
            },
        );
    }

    private function removeByFrom(?string $byline): string
    {
        return ! empty($byline) ? preg_replace('/^By /', '', $byline) : 'Unknown';
    }
}
