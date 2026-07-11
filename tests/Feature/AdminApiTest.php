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
}
