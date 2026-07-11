<?php

namespace Tests\Feature;

use App\Models\AvatarFrame;
use App\Models\SampleAvatar;
use App\Models\User;
use Database\Seeders\AvatarFrameSeeder;
use Database\Seeders\SampleAvatarSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileAvatarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SampleAvatarSeeder::class);
        $this->seed(AvatarFrameSeeder::class);
    }

    public function test_authenticated_user_can_view_profile_page(): void
    {
        $this->withoutVite();
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->get(route('profile.me'))
            ->assertOk()
            ->assertSee(__('profile.title'), false)
            ->assertSee('Premium', false);
    }

    public function test_user_can_update_name_and_free_frame(): void
    {
        $this->withoutVite();
        $user = User::factory()->create();
        $soft = AvatarFrame::query()->where('slug', 'soft')->firstOrFail();

        $this->actingAs($user, 'web')
            ->patch(route('profile.account.update'), [
                'name' => 'Nguyen Van A',
                'username' => $user->username,
                'avatar_frame_id' => $soft->id,
            ])
            ->assertRedirect(route('profile.me', ['tab' => 'account']));

        $user->refresh();
        $this->assertSame('Nguyen Van A', $user->name);
        $this->assertSame($soft->id, $user->avatar_frame_id);
    }

    public function test_premium_frame_requires_active_premium(): void
    {
        $this->withoutVite();
        $user = User::factory()->create(['premium_expires_at' => null]);
        $gold = AvatarFrame::query()->where('slug', 'gold')->firstOrFail();

        $this->actingAs($user, 'web')
            ->from(route('profile.me'))
            ->patch(route('profile.account.update'), [
                'name' => $user->name,
                'username' => $user->username,
                'avatar_frame_id' => $gold->id,
            ])
            ->assertRedirect(route('profile.me'))
            ->assertSessionHasErrors('avatar_frame_id');
    }

    public function test_user_can_enable_premium_demo_and_use_gold_frame(): void
    {
        $this->withoutVite();
        $user = User::factory()->create(['premium_expires_at' => null]);
        $gold = AvatarFrame::query()->where('slug', 'gold')->firstOrFail();

        $this->actingAs($user, 'web')
            ->post(route('profile.premium-avatar'))
            ->assertRedirect(route('profile.me', ['tab' => 'overview']));

        $user->refresh();
        $this->assertTrue($user->hasActivePremium());
        $this->assertNotNull($user->premium_expires_at);

        $this->actingAs($user, 'web')
            ->patch(route('profile.account.update'), [
                'name' => $user->name,
                'username' => $user->username,
                'avatar_frame_id' => $gold->id,
            ])
            ->assertRedirect(route('profile.me', ['tab' => 'account']));

        $this->assertSame($gold->id, $user->fresh()->avatar_frame_id);
    }

    public function test_user_can_select_sample_avatar(): void
    {
        $this->withoutVite();
        $user = User::factory()->create();
        $sample = SampleAvatar::query()->where('slug', 'explorer')->firstOrFail();

        $this->actingAs($user, 'web')
            ->patch(route('profile.account.update'), [
                'name' => $user->name,
                'username' => $user->username,
                'sample_avatar_id' => $sample->id,
            ])
            ->assertRedirect(route('profile.me', ['tab' => 'account']));

        $user->refresh();
        $this->assertSame($sample->id, $user->sample_avatar_id);
        $this->assertNull($user->avatar_path);
        $this->assertStringContainsString('sample-avatars', (string) $user->avatarUrl());
    }

    public function test_user_can_upload_avatar(): void
    {
        $this->withoutVite();
        Storage::fake('public');

        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.jpg', 600, 600);

        $this->actingAs($user, 'web')
            ->patch(route('profile.account.update'), [
                'name' => $user->name,
                'username' => $user->username,
                'avatar' => $file,
            ])
            ->assertRedirect(route('profile.me', ['tab' => 'account']));

        $user->refresh();
        $this->assertNotNull($user->avatar_path);
        $this->assertNull($user->sample_avatar_id);
        Storage::disk('public')->assertExists($user->avatar_path);
    }

    public function test_public_profile_shows_user(): void
    {
        $this->withoutVite();
        $user = User::factory()->withPremiumAvatar()->create([
            'name' => 'Premium User',
            'username' => 'premium_user',
        ]);

        $this->get(route('profile.show', 'premium_user'))
            ->assertOk()
            ->assertSee('Premium User', false)
            ->assertSee('Premium', false);
    }

    public function test_expired_premium_hides_premium_frame(): void
    {
        $gold = AvatarFrame::query()->where('slug', 'gold')->firstOrFail();
        $user = User::factory()->create([
            'premium_expires_at' => now()->subDay(),
            'avatar_frame_id' => $gold->id,
        ]);

        $this->assertFalse($user->hasActivePremium());
        $this->assertNull($user->resolvedAvatarFrame());
    }
}
