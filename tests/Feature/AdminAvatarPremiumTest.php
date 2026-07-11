<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AvatarFrame;
use App\Models\PremiumSubscription;
use App\Models\User;
use Database\Seeders\AvatarFrameSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminAvatarPremiumTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AvatarFrameSeeder::class);
        $this->admin = Admin::query()->create([
            'name' => 'Admin Test',
            'email' => 'admin-avatar@vivu.test',
            'password' => 'password',
            'is_active' => true,
        ]);
        Sanctum::actingAs($this->admin, ['*'], 'admin');
    }

    public function test_admin_can_list_avatar_frames(): void
    {
        $this->getJson('/api/admin/avatar-frames')
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('data.0.slug', 'soft');
    }

    public function test_admin_can_create_avatar_frame(): void
    {
        $this->postJson('/api/admin/avatar-frames', [
            'name' => 'Neon',
            'slug' => 'neon',
            'effect_type' => 'glow',
            'effect_config' => [
                'colors' => ['#22d3ee', '#a855f7'],
                'thickness' => 3,
                'speed_ms' => 2000,
                'intensity' => 0.8,
            ],
            'is_premium' => true,
            'show_badge' => true,
            'sort_order' => 99,
        ])
            ->assertCreated()
            ->assertJsonPath('data.slug', 'neon')
            ->assertJsonPath('data.effect_type', 'glow');

        $this->assertDatabaseHas('avatar_frames', ['slug' => 'neon']);
    }

    public function test_admin_can_grant_premium_to_user(): void
    {
        $user = User::factory()->create(['username' => 'vip_user']);

        $this->postJson('/api/admin/premium-subscriptions', [
            'username' => 'vip_user',
            'days' => 14,
            'notes' => 'Promo',
        ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'active');

        $user->refresh();
        $this->assertTrue($user->hasActivePremium());
        $this->assertNotNull($user->premium_expires_at);
        $this->assertTrue($user->premium_expires_at->greaterThan(now()->addDays(13)));
    }

    public function test_admin_can_grant_lifetime_premium(): void
    {
        $user = User::factory()->create();

        $this->patchJson("/api/admin/users/{$user->id}/premium", [
            'lifetime' => true,
            'notes' => 'VIP',
        ])
            ->assertCreated();

        $user->refresh();
        $this->assertTrue($user->hasActivePremium());
        $this->assertTrue($user->premium_expires_at->greaterThan(now()->addYears(40)));
    }

    public function test_admin_can_cancel_subscription(): void
    {
        $user = User::factory()->create();
        $sub = PremiumSubscription::query()->create([
            'user_id' => $user->id,
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
            'status' => 'active',
            'source' => 'admin',
        ]);
        $user->forceFill(['premium_expires_at' => now()->addDays(30)])->save();

        $this->patchJson("/api/admin/premium-subscriptions/{$sub->id}", [
            'action' => 'cancel',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');

        $user->refresh();
        $this->assertFalse($user->hasActivePremium());
    }

    public function test_admin_can_update_frame(): void
    {
        $frame = AvatarFrame::query()->where('slug', 'gold')->firstOrFail();

        $this->patchJson("/api/admin/avatar-frames/{$frame->id}", [
            'name' => 'Hoàng kim Pro',
            'is_active' => true,
        ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Hoàng kim Pro');
    }
}
