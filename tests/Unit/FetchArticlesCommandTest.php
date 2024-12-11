<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FetchArticlesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_fetches_articles_from_news_api()
    {
        Http::fake([
            'https://newsapi.org/v2/top-headlines*' => Http::response([
                'articles' => [
                    [
                        'title' => 'NewsAPI Article 1',
                        'content' => 'Content of NewsAPI article 1.',
                        'author' => 'Author 1',
                        'source' => ['name' => 'NewsAPI Source'],
                        'publishedAt' => now()->toDateTimeString(),
                    ],
                ],
            ]),
        ]);

        Cache::shouldReceive('remember')
            ->once()
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });

        $this->artisan('articles:fetch news-api')
            ->expectsOutput('Fetching articles from source: news-api')
            ->expectsOutput('Fetched articles from articles:newsapi.')
            ->expectsOutput('Articles fetched and stored successfully!')
            ->assertExitCode(0);

        $this->assertDatabaseHas('articles', [
            'title' => 'NewsAPI Article 1',
            'content' => 'Content of NewsAPI article 1.',
            'author' => 'Author 1',
            'source' => 'NewsAPI Source',
        ]);
    }

    public function test_it_fetches_articles_from_guardian()
    {
        Http::fake([
            'https://content.guardianapis.com/search*' => Http::response([
                'response' => [
                    'results' => [
                        [
                            'webTitle' => 'Guardian Article 1',
                            'fields' => [
                                'bodyText' => 'Content of Guardian article 1.',
                                'byline' => 'By Author 2',
                            ],
                            'sectionName' => 'World',
                            'webPublicationDate' => now()->toDateTimeString(),
                        ],
                    ],
                ],
            ]),
        ]);

        Cache::shouldReceive('remember')
            ->once()
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });

        $this->artisan('articles:fetch guardian')
            ->expectsOutput('Fetching articles from source: guardian')
            ->expectsOutput('Fetched articles from articles:guardian.')
            ->expectsOutput('Articles fetched and stored successfully!')
            ->assertExitCode(0);

        $this->assertDatabaseHas('articles', [
            'title' => 'Guardian Article 1',
            'content' => 'Content of Guardian article 1.',
            'author' => 'Author 2',
            'source' => 'The Guardian',
            'category' => 'World',
        ]);
    }

    public function test_it_fetches_articles_from_ny_times()
    {
        Http::fake([
            'https://api.nytimes.com/svc/topstories/v2/home.json*' => Http::response([
                'results' => [
                    [
                        'title' => 'NYT Article 1',
                        'abstract' => 'Content of NYT article 1.',
                        'byline' => 'By Author 3',
                        'section' => 'Technology',
                        'published_date' => now()->toDateTimeString(),
                    ],
                ],
            ]),
        ]);

        Cache::shouldReceive('remember')
            ->once()
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });

        $this->artisan('articles:fetch ny-times')
            ->expectsOutput('Fetching articles from source: ny-times')
            ->expectsOutput('Fetched articles from articles:nyt.')
            ->expectsOutput('Articles fetched and stored successfully!')
            ->assertExitCode(0);

        $this->assertDatabaseHas('articles', [
            'title' => 'NYT Article 1',
            'content' => 'Content of NYT article 1.',
            'author' => 'Author 3',
            'source' => 'New York Times',
            'category' => 'Technology',
        ]);
    }

    public function test_it_fetches_articles_from_all_sources()
    {
        Http::fake([
            'https://newsapi.org/v2/top-headlines*' => Http::response([
                'articles' => [
                    [
                        'title' => 'NewsAPI Article',
                        'content' => 'Content of NewsAPI article.',
                        'author' => 'NewsAPI Author',
                        'source' => ['name' => 'NewsAPI Source'],
                        'publishedAt' => now()->toDateTimeString(),
                    ],
                ],
            ]),
            'https://content.guardianapis.com/search*' => Http::response([
                'response' => [
                    'results' => [
                        [
                            'webTitle' => 'Guardian Article',
                            'fields' => [
                                'bodyText' => 'Content of Guardian article.',
                                'byline' => 'By Guardian Author',
                            ],
                            'sectionName' => 'World',
                            'webPublicationDate' => now()->toDateTimeString(),
                        ],
                    ],
                ],
            ]),
            'https://api.nytimes.com/svc/topstories/v2/home.json*' => Http::response([
                'results' => [
                    [
                        'title' => 'NYT Article',
                        'abstract' => 'Content of NYT article.',
                        'byline' => 'By NYT Author',
                        'section' => 'Technology',
                        'published_date' => now()->toDateTimeString(),
                    ],
                ],
            ]),
        ]);

        Cache::shouldReceive('remember')
            ->times(3) // Called once per source
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });

        $this->artisan('articles:fetch all')
            ->expectsOutput('Fetching articles from source: all')
            ->expectsOutput('Fetched articles from articles:newsapi.')
            ->expectsOutput('Fetched articles from articles:guardian.')
            ->expectsOutput('Fetched articles from articles:nyt.')
            ->expectsOutput('Articles fetched and stored successfully!')
            ->assertExitCode(0);

        $this->assertDatabaseHas('articles', [
            'title' => 'NewsAPI Article',
            'author' => 'NewsAPI Author',
        ]);
        $this->assertDatabaseHas('articles', [
            'title' => 'Guardian Article',
            'author' => 'Guardian Author',
        ]);
        $this->assertDatabaseHas('articles', [
            'title' => 'NYT Article',
            'author' => 'NYT Author',
        ]);
    }
}
