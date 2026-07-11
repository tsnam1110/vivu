<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Nguyen Van A',
            'username' => 'nguyenvana',
            'email' => 'a@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated('web');
        $this->assertDatabaseHas('users', ['email' => 'a@example.com', 'username' => 'nguyenvana']);
        $this->assertDatabaseHas('user_profiles', ['user_id' => User::query()->first()->id]);
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $this->post('/login', [
            'email' => 'login@example.com',
            'password' => 'password123',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user, 'web');

        $this->post('/logout')->assertRedirect(route('home'));
        $this->assertGuest('web');
    }

    public function test_suspended_user_cannot_login(): void
    {
        User::factory()->suspended()->create([
            'email' => 'bad@example.com',
            'password' => 'password123',
        ]);

        $this->from('/login')->post('/login', [
            'email' => 'bad@example.com',
            'password' => 'password123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest('web');
    }

    public function test_admin_can_login_and_receive_token(): void
    {
        $admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.admin.email', 'admin@example.com')
            ->assertJsonStructure(['data' => ['token', 'admin']]);

        $token = $response->json('data.token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/admin/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'admin@example.com');
    }

    public function test_user_cannot_access_admin_api(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/admin/users');

        $this->assertTrue(
            in_array($response->status(), [401, 403], true),
            'User guard must not access admin API, got '.$response->status()
        );

    }

    public function test_invalid_admin_token_is_rejected(): void
    {
        $this->withHeader('Authorization', 'Bearer invalid-token')
            ->getJson('/api/admin/me')
            ->assertUnauthorized();
    }

    public function test_inactive_admin_cannot_login(): void
    {
        Admin::factory()->inactive()->create([
            'email' => 'off@example.com',
            'password' => 'password123',
        ]);

        $this->postJson('/api/admin/login', [
            'email' => 'off@example.com',
            'password' => 'password123',
        ])->assertStatus(422);
    }
}
