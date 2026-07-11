<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Dish;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileBodyMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_save_body_metrics(): void
    {
        $this->withoutVite();
        $user = User::factory()->create();
        $user->profile?->update([
            'personality' => [],
            'interests' => [],
        ]);

        $this->actingAs($user, 'web')
            ->patch(route('profile.update'), [
                '_tab' => 'body',
                'weight_kg' => 62.5,
                'height_cm' => 168,
                'gender' => 'female',
                'birth_year' => 1995,
                'activity_level' => 'light',
            ])
            ->assertRedirect(route('profile.me', ['tab' => 'body']));

        $profile = $user->fresh()->profile;
        $this->assertSame(62.5, (float) $profile->weight_kg);
        $this->assertSame(168, $profile->height_cm);
        $this->assertSame('female', $profile->gender->value);
        $this->assertSame(1995, $profile->birth_year);
        $this->assertSame('light', $profile->activity_level->value);
    }

    public function test_profile_popup_shows_body_section(): void
    {
        $this->withoutVite();
        $user = User::factory()->create();
        $user->profile?->update(['weight_kg' => 55]);

        $this->actingAs($user, 'web')
            ->get(route('profile.me', ['tab' => 'body']))
            ->assertOk()
            ->assertSee(__('profile.title'), false)
            ->assertSee(__('profile.body_section'), false)
            ->assertSee('weight_kg', false);
    }

    public function test_old_profile_edit_redirects_to_popup(): void
    {
        $this->withoutVite();
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->get(route('profile.edit'))
            ->assertRedirect(route('profile.me', ['tab' => 'taste']));
    }

    public function test_suggest_accepts_target_calories(): void
    {
        $user = User::factory()->create();
        $user->profile?->update(['weight_kg' => 70]);

        Dish::factory()->count(3)->create([
            'meal_slots' => ['lunch'],
            'supports_main' => true,
            'supports_dine_out' => true,
            'calories_kcal' => 500,
            'serving_grams' => 350,
        ]);

        $this->actingAs($user, 'web')
            ->postJson(route('what-to-eat.suggest'), [
                'meal_slot' => 'lunch',
                'meal_size' => 'main',
                'meal_mode' => 'dine_out',
                'count' => 2,
                'target_calories' => 1500,
            ])
            ->assertOk()
            ->assertJsonPath('meta.target_calories', 1500)
            ->assertJsonPath('meta.meal_budget', 525); // 35% of 1500
    }
}
