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

    public function test_web_create_form_renders_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        Category::factory()->create();

        $this->actingAs($user, 'web')
            ->get(route('experiences.create'))
            ->assertOk()
            ->assertSee('Đăng trải nghiệm', false)
            ->assertSee('name="title"', false)
            ->assertSee('experience-map', false)
            ->assertSee('Lưu nháp', false)
            ->assertSee('Đăng công khai', false);
    }

    public function test_web_user_can_store_published_experience(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $tag = Tag::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($user, 'web')->post(route('experiences.store'), [
            'title' => 'Cà phê view biển Đà Nẵng',
            'content' => 'View đẹp lúc hoàng hôn',
            'category_id' => $category->id,
            'tags' => [$tag->id],
            'place_name' => 'Café Horizon',
            'address' => 'Vo Nguyen Giap, Da Nang',
            'latitude' => 16.0544,
            'longitude' => 108.2022,
            'status' => 'published',
        ]);

        $experience = Experience::query()->where('title', 'Cà phê view biển Đà Nẵng')->first();
        $this->assertNotNull($experience);
        $response->assertRedirect(route('experiences.show', $experience->slug));
        $this->assertSame(ExperienceStatus::Published, $experience->status);
        $this->assertTrue($experience->tags->contains('id', $tag->id));
    }

    public function test_web_user_can_store_draft_without_coordinates(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $this->actingAs($user, 'web')->post(route('experiences.store'), [
            'title' => 'Nháp chưa có toạ độ',
            'category_id' => $category->id,
            'status' => 'draft',
        ])->assertRedirect();

        $this->assertDatabaseHas('experiences', [
            'title' => 'Nháp chưa có toạ độ',
            'user_id' => $user->id,
            'status' => ExperienceStatus::Draft->value,
        ]);
    }

    public function test_user_can_create_experience_with_author_rating_and_new_pending_tag(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user, 'web')->post(route('experiences.store'), [
            'title' => 'Quán có thẻ mới',
            'category_id' => $category->id,
            'latitude' => 16.05,
            'longitude' => 108.20,
            'status' => 'published',
            'author_rating' => 9, // 4.5 sao
            'new_tags' => ['Siêu chill Đà Nẵng'],
        ]);

        $experience = Experience::query()->where('title', 'Quán có thẻ mới')->first();
        $this->assertNotNull($experience);
        $response->assertRedirect(route('experiences.show', $experience->slug));
        $this->assertSame(9, $experience->author_rating); // 4.5 / 5 sao

        $tag = Tag::query()->where('name', 'Siêu chill Đà Nẵng')->first();
        $this->assertNotNull($tag);
        $this->assertSame(\App\Enums\TagStatus::Pending, $tag->status);
        $this->assertSame($user->id, $tag->created_by);
        $this->assertTrue($experience->tags->contains('id', $tag->id));
    }

    public function test_pending_tag_not_listed_on_public_tags_api(): void
    {
        $user = User::factory()->create();
        Tag::factory()->create(['name' => 'Public Tag', 'status' => \App\Enums\TagStatus::Approved]);
        Tag::factory()->pending()->create([
            'name' => 'Secret Tag',
            'created_by' => $user->id,
        ]);

        $this->getJson('/api/tags')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Public Tag'])
            ->assertJsonMissing(['name' => 'Secret Tag']);
    }
}
