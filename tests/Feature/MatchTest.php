<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Services\MatchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_higher_overlap_scores_higher(): void
    {
        $service = app(MatchService::class);

        $high = $service->score(
            ['huong-ngoai', 'phieu-luu'],
            ['huong-ngoai', 'phieu-luu'],
            ['am-thuc', 'nhiep-anh'],
            ['am-thuc', 'nhiep-anh'],
        );

        $low = $service->score(
            ['huong-ngoai', 'phieu-luu'],
            ['yen-tinh'],
            ['am-thuc'],
            ['the-thao'],
        );

        $this->assertGreaterThan($low, $high);
    }

    public function test_matches_exclude_self_and_below_threshold(): void
    {
        $me = User::factory()->create();
        $me->load('profile')->profile->update([
            'personality' => ['huong-ngoai', 'phieu-luu'],
            'interests' => ['am-thuc', 'nhiep-anh'],
            'is_matchable' => true,
        ]);

        $twin = User::factory()->create();
        $twin->load('profile')->profile->update([
            'personality' => ['huong-ngoai', 'phieu-luu'],
            'interests' => ['am-thuc', 'nhiep-anh'],
            'is_matchable' => true,
        ]);

        $stranger = User::factory()->create();
        $stranger->load('profile')->profile->update([
            'personality' => ['yen-tinh'],
            'interests' => ['the-thao'],
            'is_matchable' => true,
        ]);


        $response = $this->actingAs($me, 'web')->getJson('/api/users/matches');

        $response->assertOk();
        $usernames = collect($response->json('data'))->pluck('username')->all();

        $this->assertContains($twin->username, $usernames);
        $this->assertNotContains($me->username, $usernames);
        $this->assertNotContains($stranger->username, $usernames);

        $match = collect($response->json('data'))->firstWhere('username', $twin->username);
        $this->assertNotEmpty($match['shared_traits']);
        $this->assertContains('am-thuc', $match['shared_traits']);
    }

    public function test_hidden_profile_not_matchable(): void
    {
        $me = User::factory()->create();
        $me->load('profile')->profile->update([
            'personality' => ['huong-ngoai'],
            'interests' => ['am-thuc'],
            'is_matchable' => true,
        ]);

        $hidden = User::factory()->create();
        $hidden->load('profile')->profile->update([
            'personality' => ['huong-ngoai'],
            'interests' => ['am-thuc'],
            'is_matchable' => false,
        ]);


        $response = $this->actingAs($me, 'web')->getJson('/api/users/matches');
        $usernames = collect($response->json('data'))->pluck('username')->all();
        $this->assertNotContains($hidden->username, $usernames);
    }
}
