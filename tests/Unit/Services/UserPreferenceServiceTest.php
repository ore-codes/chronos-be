<?php

namespace Tests\Unit\Services;

use App\Models\Article;
use App\Models\User;
use App\Models\UserPreference;
use App\Services\UserPreferenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class UserPreferenceServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserPreferenceService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = new UserPreferenceService();
    }

    public function test_it_saves_user_preferences()
    {
        $user = User::factory()->create();
        Article::factory()->create(['author' => 'Jane Doe', 'source' => 'CNN', 'category' => 'us']);
        Article::factory()->create(['author' => 'Jane Doe', 'source' => 'New York Times', 'category' => 'us']);

        $request = Request::create('/api/preferences', 'POST', [
            'sources' => ['CNN', 'New York Times'],
            'categories' => ['us'],
            'authors' => ['John Doe'],
        ]);
        $request->setUserResolver(fn() => $user);

        $preferences = $this->service->savePreferences($request);

        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $user->id,
            'sources' => json_encode(['CNN', 'New York Times']),
            'categories' => json_encode(['us']),
            'authors' => json_encode(['John Doe']),
        ]);

        $this->assertInstanceOf(UserPreference::class, $preferences);
        $this->assertEquals(['CNN', 'New York Times'], $preferences->sources);
        $this->assertEquals(['us'], $preferences->categories);
        $this->assertEquals(['John Doe'], $preferences->authors);
    }

    public function test_it_returns_matching_authors()
    {
        Article::factory()->create(['author' => 'John Doe']);
        Article::factory()->create(['author' => 'Jane Doe']);
        Article::factory()->create(['author' => 'Jonathan Smith']);
        Article::factory()->create(['author' => 'Unrelated Author']);

        $request = Request::create('/api/authors/search', 'GET', ['q' => 'John']);

        $authors = $this->service->searchAuthors($request);

        $this->assertCount(1, $authors);
        $this->assertTrue($authors->contains('John Doe'));
        $this->assertFalse($authors->contains('Unrelated Author'));
    }

    public function test_it_returns_no_authors_if_no_match_found()
    {
        Article::factory()->create(['author' => 'Jane Doe']);
        Article::factory()->create(['author' => 'Unrelated Author']);

        $request = Request::create('/api/authors/search', 'GET', ['q' => 'NonExistentAuthor']);

        $authors = $this->service->searchAuthors($request);

        $this->assertCount(0, $authors);
    }
}
