<?php

namespace Tests\Feature\Api;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_post_requires_authentication(): void
    {
        $response = $this->postJson('/api/posts', [
            'title' => 'Test',
            'text' => 'Text',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function test_authenticated_user_can_create_post(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/posts', [
            'title' => 'My Post',
            'text' => 'Post body',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'title', 'text', 'author', 'created_at'],
            ])
            ->assertJsonPath('data.title', 'My Post');

        $this->assertDatabaseHas('posts', [
            'user_id' => $user->id,
            'title' => 'My Post',
        ]);
    }

    public function test_create_post_fails_with_empty_data(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/posts', []);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['errors' => ['title', 'text']]);
    }

    public function test_index_returns_paginated_posts(): void
    {
        Sanctum::actingAs(User::factory()->create());
        Post::factory()->count(20)->create();

        $response = $this->getJson('/api/posts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'per_page', 'total'],
                'links',
            ]);

        $this->assertCount(15, $response->json('data')); // дефолт limit=15
    }

    public function test_index_supports_limit_and_offset(): void
    {
        Sanctum::actingAs(User::factory()->create());
        Post::factory()->count(20)->create();

        $response = $this->getJson('/api/posts?limit=5&offset=5');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data'));
        $this->assertEquals(2, $response->json('meta.current_page'));
    }

    public function test_index_supports_sort_by_title(): void
    {
        Sanctum::actingAs(User::factory()->create());

        Post::factory()->create(['title' => 'Zebra']);
        Post::factory()->create(['title' => 'Apple']);
        Post::factory()->create(['title' => 'Mango']);

        $response = $this->getJson('/api/posts?sort=title_asc');

        $response->assertStatus(200);
        $titles = array_column($response->json('data'), 'title');
        $this->assertEquals(['Apple', 'Mango', 'Zebra'], $titles);
    }

    public function test_index_supports_date_filter(): void
    {
        Sanctum::actingAs(User::factory()->create());

        Post::factory()->create([
            'title' => 'Old',
            'created_at' => now()->subDays(10),
        ]);
        Post::factory()->create([
            'title' => 'New',
            'created_at' => now(),
        ]);

        $from = now()->subDays(2)->toDateString();
        $response = $this->getJson("/api/posts?date_from={$from}");

        $response->assertStatus(200);
        $titles = array_column($response->json('data'), 'title');
        $this->assertContains('New', $titles);
        $this->assertNotContains('Old', $titles);
    }

    public function test_index_rejects_invalid_sort(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson('/api/posts?sort=invalid');

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['sort']]);
    }

    public function test_my_posts_returns_only_own_posts(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();

        Post::factory()->count(3)->for($me)->create();
        Post::factory()->count(5)->for($other)->create();

        Sanctum::actingAs($me);

        $response = $this->getJson('/api/my-posts');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }
}
