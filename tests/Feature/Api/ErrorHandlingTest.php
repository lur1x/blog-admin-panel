<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_validation_error_returns_standard_json(): void
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
            ->assertJsonStructure(['success', 'message', 'errors'])
            ->assertJsonPath('success', false);
    }

    public function test_unauthenticated_returns_standard_json(): void
    {
        $response = $this->getJson('/api/posts');

        $response->assertStatus(401)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Не аутентифицирован.');
    }

    public function test_not_found_returns_standard_json(): void
    {
        $response = $this->getJson('/api/nonexistent');

        $response->assertStatus(404)
            ->assertJsonPath('success', false);
    }
}
