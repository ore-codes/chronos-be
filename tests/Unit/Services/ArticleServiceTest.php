<?php

namespace Tests\Unit\Services;

use App\Models\Article;
use App\Models\User;
use App\Models\UserPreference;
use App\Services\ArticleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ArticleServiceTest extends TestCase
{
    use RefreshDatabase;

    private ArticleService $articleService;

    public function setUp(): void
    {
        parent::setUp();
        $this->articleService = new ArticleService();
    }

    public function test_it_fetches_all_articles_without_preferences_or_filters()
    {
        Article::factory()->count(15)->create();

        $user = User::factory()->create();
        $request = Request::create('/api/articles');
        $request->setUserResolver(fn() => $user);

        $articles = $this->articleService->getAllArticles($request);

        $this->assertCount(9, $articles);
        $this->assertEquals(15, $articles->total());
    }

    public function test_it_applies_user_preferences_to_fetch_articles()
    {
        Article::factory()->create(['source' => 'CNN', 'category' => 'Technology', 'author' => 'John Doe']);
        Article::factory()->create(['source' => 'BBC', 'category' => 'Health', 'author' => 'Jane Doe']);
        Article::factory()->create(['source' => 'Fox News', 'category' => 'Sports', 'author' => 'Jonathan Doe']);

        $user = User::factory()->create();
        UserPreference::factory()->create([
            'user_id' => $user->id,
            'sources' => ['CNN', 'BBC'],
            'categories' => ['Technology', 'Health'],
            'authors' => ['John Doe', 'Jane Doe'],
        ]);

        $request = Request::create('/api/articles', 'GET');
        $request->setUserResolver(fn() => $user);

        $articles = $this->articleService->getAllArticles($request);

        $this->assertCount(2, $articles); // Only CNN and BBC articles should match
        $this->assertTrue($articles->pluck('source')->contains('CNN'));
        $this->assertTrue($articles->pluck('source')->contains('BBC'));
    }

    public function test_it_filters_articles_by_date_category_and_source()
    {
        Article::factory()->create(['source' => 'CNN', 'category' => 'Technology', 'published_at' => '2024-12-01']);
        Article::factory()->create(['source' => 'BBC', 'category' => 'Health', 'published_at' => '2024-12-02']);
        Article::factory()->create(['source' => 'Fox News', 'category' => 'Sports', 'published_at' => '2024-12-03']);

        $user = User::factory()->create();
        $request = Request::create('/api/articles', 'GET', [
            'date' => '2024-12-01',
            'category' => 'Technology',
            'source' => 'CNN',
        ]);
        $request->setUserResolver(fn() => $user);

        $articles = $this->articleService->getAllArticles($request);

        $this->assertCount(1, $articles);
        $this->assertEquals('CNN', $articles->first()->source);
        $this->assertEquals('Technology', $articles->first()->category);
    }

    public function test_it_searches_articles_by_keyword()
    {
        Article::factory()->create(['title' => 'Breaking News on Technology']);
        Article::factory()->create(['title' => 'Health Updates from BBC']);
        Article::factory()->create(['title' => 'Sports Highlights by Fox News']);

        $user = User::factory()->create();
        $request = Request::create('/api/articles', 'GET', ['keyword' => 'Technology']);
        $request->setUserResolver(fn() => $user);

        $articles = $this->articleService->getAllArticles($request);

        $this->assertCount(1, $articles);
        $this->assertEquals('Breaking News on Technology', $articles->first()->title);
    }
}
