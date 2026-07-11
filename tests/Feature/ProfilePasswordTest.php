<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfilePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_change_password(): void
    {
        $this->withoutVite();
        $user = User::factory()->create([
            'password' => 'password123',
        ]);

        $this->actingAs($user, 'web')
            ->patch(route('profile.password.update'), [
                'current_password' => 'password123',
                'password' => 'new-secret-99',
                'password_confirmation' => 'new-secret-99',
            ])
            ->assertRedirect(route('profile.me', ['tab' => 'security']))
            ->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('new-secret-99', $user->password));
    }

    public function test_wrong_current_password_is_rejected(): void
    {
        $this->withoutVite();
        $user = User::factory()->create([
            'password' => 'password123',
        ]);

        $this->actingAs($user, 'web')
            ->from(route('profile.me'))
            ->patch(route('profile.password.update'), [
                'current_password' => 'wrong-password',
                'password' => 'new-secret-99',
                'password_confirmation' => 'new-secret-99',
            ])
            ->assertRedirect(route('profile.me'))
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('password123', $user->fresh()->password));
    }

    public function test_password_confirmation_must_match(): void
    {
        $this->withoutVite();
        $user = User::factory()->create([
            'password' => 'password123',
        ]);

        $this->actingAs($user, 'web')
            ->from(route('profile.me'))
            ->patch(route('profile.password.update'), [
                'current_password' => 'password123',
                'password' => 'new-secret-99',
                'password_confirmation' => 'different',
            ])
            ->assertRedirect(route('profile.me'))
            ->assertSessionHasErrors('password');
    }

    public function test_profile_page_shows_password_button(): void
    {
        $this->withoutVite();
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->get(route('profile.me'))
            ->assertOk()
            ->assertSee('Đổi mật khẩu', false)
            ->assertSee('Bảo vệ tài khoản', false);
    }
}
