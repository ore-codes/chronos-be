<?php

namespace Tests\Unit;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_fetch_articles()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        Article::factory()->count(5)->create();

        $response = $this->getJson('/api/articles');
        $response->assertStatus(200)
            ->assertJsonCount(5, 'data'); // Assuming paginated response
    }
}
