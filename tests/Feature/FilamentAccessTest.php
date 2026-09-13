<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_filament_panel(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin);

        $response = $this->get('/admin');
        $response->assertStatus(200);
    }

    public function test_regular_user_cannot_access_filament_panel(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user);

        $response = $this->get('/admin');
        // Аутентифицированный, но без прав — Filament отдаёт 403
        $response->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/admin/login');
    }
}
