<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Experience;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentReactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_comment_with_rating(): void
    {
        $user = User::factory()->create();
        $experience = Experience::factory()->create();

        $this->actingAs($user, 'web')
            ->postJson('/api/experiences/'.$experience->id.'/comments', [
                'body' => 'Rất tuyệt!',
                'rating' => 5,
            ])
            ->assertCreated()
            ->assertJsonPath('data.body', 'Rất tuyệt!')
            ->assertJsonPath('data.rating', 5);

        $experience->refresh();
        $this->assertEquals(5.0, (float) $experience->rating_avg);
        $this->assertEquals(1, $experience->rating_count);
    }

    public function test_rating_out_of_range_fails(): void
    {
        $user = User::factory()->create();
        $experience = Experience::factory()->create();

        $this->actingAs($user, 'web')
            ->postJson('/api/experiences/'.$experience->id.'/comments', [
                'body' => 'Bad rating',
                'rating' => 9,
            ])
            ->assertStatus(422);
    }

    public function test_guest_cannot_comment_or_react(): void
    {
        $experience = Experience::factory()->create();

        $this->postJson('/api/experiences/'.$experience->id.'/comments', [
            'body' => 'Hi',
        ])->assertUnauthorized();

        $this->postJson('/api/experiences/'.$experience->id.'/reactions', [
            'type' => 'like',
        ])->assertUnauthorized();
    }

    public function test_reaction_upsert_and_toggle(): void
    {
        $user = User::factory()->create();
        $experience = Experience::factory()->create();

        $this->actingAs($user, 'web')
            ->postJson('/api/experiences/'.$experience->id.'/reactions', ['type' => 'like'])
            ->assertOk()
            ->assertJsonPath('data.type', 'like')
            ->assertJsonPath('data.total', 1);

        $this->assertDatabaseCount('reactions', 1);
        $experience->refresh();
        $this->assertEquals(1, $experience->reaction_count);

        // change type
        $this->actingAs($user, 'web')
            ->postJson('/api/experiences/'.$experience->id.'/reactions', ['type' => 'love'])
            ->assertOk()
            ->assertJsonPath('data.type', 'love')
            ->assertJsonPath('data.total', 1);

        $this->assertDatabaseCount('reactions', 1);

        // toggle off same type
        $this->actingAs($user, 'web')
            ->postJson('/api/experiences/'.$experience->id.'/reactions', ['type' => 'love'])
            ->assertOk()
            ->assertJsonPath('data.type', null)
            ->assertJsonPath('data.total', 0);

        $experience->refresh();
        $this->assertEquals(0, $experience->reaction_count);
    }

    public function test_owner_can_delete_comment_and_rating_recalculates(): void
    {
        $user = User::factory()->create();
        $experience = Experience::factory()->create();
        $comment = Comment::factory()->create([
            'experience_id' => $experience->id,
            'user_id' => $user->id,
            'rating' => 4,
        ]);

        app(\App\Services\CommentService::class)->recalculateRating($experience);
        $experience->refresh();
        $this->assertEquals(1, $experience->rating_count);

        $this->actingAs($user, 'web')
            ->deleteJson('/api/comments/'.$comment->id)
            ->assertNoContent();

        $experience->refresh();
        $this->assertEquals(0, $experience->rating_count);
        $this->assertEquals(0.0, (float) $experience->rating_avg);
    }
}
