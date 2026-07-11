<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\CommentStatus;
use App\Enums\ExperienceStatus;
use App\Enums\UserStatus;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Experience;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminApiTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(?Admin $admin = null): Admin
    {
        $admin ??= Admin::factory()->create();
        Sanctum::actingAs($admin, ['*'], 'admin');

        return $admin;
    }

    public function test_admin_can_crud_categories(): void
    {
        $this->actingAsAdmin();

        $create = $this->postJson('/api/admin/categories', [
            'name' => 'Ăn vặt',
            'icon' => '🍢',
        ])->assertCreated();

        $id = $create->json('data.id');

        $this->patchJson('/api/admin/categories/'.$id, [
            'is_active' => false,
        ])->assertOk()->assertJsonPath('data.is_active', false);

        $this->getJson('/api/admin/categories')->assertOk();
    }

    public function test_admin_cannot_delete_category_with_experiences(): void
    {
        $this->actingAsAdmin();
        $category = Category::factory()->create();
        Experience::factory()->create(['category_id' => $category->id]);

        $this->deleteJson('/api/admin/categories/'.$category->id)
            ->assertStatus(422);
    }

    public function test_admin_can_hide_experience_and_comment(): void
    {
        $this->actingAsAdmin();
        $experience = Experience::factory()->create(['status' => ExperienceStatus::Published]);
        $comment = Comment::factory()->create(['experience_id' => $experience->id]);

        $this->patchJson('/api/admin/experiences/'.$experience->id, [
            'status' => 'hidden',
        ])->assertOk()->assertJsonPath('data.status', 'hidden');

        $this->patchJson('/api/admin/comments/'.$comment->id, [
            'status' => CommentStatus::Hidden->value,
        ])->assertOk()->assertJsonPath('data.status', 'hidden');
    }

    public function test_admin_can_suspend_user(): void
    {
        $this->actingAsAdmin();
        $user = User::factory()->create();

        $this->patchJson('/api/admin/users/'.$user->id, [
            'status' => UserStatus::Suspended->value,
        ])->assertOk();

        $this->assertEquals(UserStatus::Suspended, $user->fresh()->status);
    }

    public function test_admin_list_filters_by_date_preset_this_month(): void
    {
        $this->actingAsAdmin();

        $thisMonth = Experience::factory()->create([
            'status' => ExperienceStatus::Published,
            'created_at' => now()->startOfMonth()->addDay(),
        ]);
        $lastMonth = Experience::factory()->create([
            'status' => ExperienceStatus::Published,
            'created_at' => now()->subMonthNoOverflow()->startOfMonth()->addDay(),
        ]);

        $ids = collect(
            $this->getJson('/api/admin/experiences?date_preset=this_month')
                ->assertOk()
                ->json('data')
        )->pluck('id');

        $this->assertTrue($ids->contains($thisMonth->id));
        $this->assertFalse($ids->contains($lastMonth->id));
    }

    public function test_admin_list_filters_users_by_status_and_search(): void
    {
        $this->actingAsAdmin();

        $active = User::factory()->create([
            'username' => 'filteruser_active',
            'status' => UserStatus::Active,
            'created_at' => now(),
        ]);
        User::factory()->create([
            'username' => 'filteruser_suspended',
            'status' => UserStatus::Suspended,
            'created_at' => now(),
        ]);

        $ids = collect(
            $this->getJson('/api/admin/users?status=active&q=filteruser&date_preset=this_month')
                ->assertOk()
                ->json('data')
        )->pluck('id');

        $this->assertTrue($ids->contains($active->id));
        $this->assertCount(1, $ids);
    }
}
