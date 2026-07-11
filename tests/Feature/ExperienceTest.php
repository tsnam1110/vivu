<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ExperienceStatus;
use App\Models\Category;
use App\Models\Experience;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_published_experience(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $tag = Tag::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($user, 'web')->postJson('/api/experiences', [
            'title' => 'Quán bún ngon Đà Nẵng',
            'content' => 'Rất ngon',
            'category_id' => $category->id,
            'tags' => [$tag->id],
            'place_name' => 'Quán Bún A',
            'address' => '123 Đường ABC',
            'latitude' => 16.0544,
            'longitude' => 108.2022,
            'status' => 'published',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Quán bún ngon Đà Nẵng')
            ->assertJsonPath('data.status', 'published');

        $this->assertDatabaseHas('experiences', [
            'title' => 'Quán bún ngon Đà Nẵng',
            'user_id' => $user->id,
        ]);
    }

    public function test_published_without_coordinates_fails(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $this->actingAs($user, 'web')->postJson('/api/experiences', [
            'title' => 'Thiếu toạ độ',
            'category_id' => $category->id,
            'status' => 'published',
        ])->assertStatus(422);
    }

    public function test_invalid_coordinates_fail_validation(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $this->actingAs($user, 'web')->postJson('/api/experiences', [
            'title' => 'Sai toạ độ',
            'category_id' => $category->id,
            'latitude' => 120,
            'longitude' => 108,
            'status' => 'draft',
        ])->assertStatus(422)->assertJsonValidationErrors('latitude');
    }

    public function test_other_user_cannot_update_experience(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $experience = Experience::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other, 'web')
            ->patchJson('/api/experiences/'.$experience->id, ['title' => 'Hacked'])
            ->assertForbidden();
    }

    public function test_list_filters_by_category_and_nearby(): void
    {
        $category = Category::factory()->create(['slug' => 'an']);
        $otherCategory = Category::factory()->create(['slug' => 'uong']);

        Experience::factory()->create([
            'category_id' => $category->id,
            'latitude' => 16.05,
            'longitude' => 108.20,
            'status' => ExperienceStatus::Published,
            'published_at' => now(),
        ]);

        Experience::factory()->create([
            'category_id' => $otherCategory->id,
            'latitude' => 10.76,
            'longitude' => 106.66,
            'status' => ExperienceStatus::Published,
            'published_at' => now(),
        ]);

        $this->getJson('/api/experiences?category=an')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->getJson('/api/experiences?lat=16.05&lng=108.20&radius_km=10')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_guest_can_view_published_experience(): void
    {
        $experience = Experience::factory()->create([
            'title' => 'Public Exp',
            'status' => ExperienceStatus::Published,
        ]);

        $this->getJson('/api/experiences/'.$experience->slug)
            ->assertOk()
            ->assertJsonPath('data.title', 'Public Exp');
    }
}
