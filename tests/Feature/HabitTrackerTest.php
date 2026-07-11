<?php

namespace Tests\Feature;

use App\Enums\HabitEntryStatus;
use App\Models\Admin;
use App\Models\HabitEntry;
use App\Models\HabitEntryHistory;
use App\Models\HabitItem;
use App\Models\User;
use App\Models\UserHabitItem;
use App\Services\HabitService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class HabitTrackerTest extends TestCase
{
    use RefreshDatabase;

    private function makeTemplate(string $name = 'Tập thể dục', bool $active = true): HabitItem
    {
        return HabitItem::query()->create([
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name).'-'.uniqid(),
            'icon' => '💪',
            'sort_order' => 1,
            'is_active' => $active,
        ]);
    }

    public function test_guest_is_redirected_from_habits(): void
    {
        $this->withoutVite();
        $this->get(route('habits.index'))->assertRedirect(route('login'));
    }

    public function test_user_adopts_template_and_creates_custom(): void
    {
        $this->withoutVite();
        $user = User::factory()->create();
        $template = $this->makeTemplate('Uống nước');

        $this->actingAs($user, 'web')
            ->post(route('habits.items.store'), [
                'mode' => 'template',
                'template_habit_item_id' => $template->id,
            ])
            ->assertRedirect(route('habits.items'));

        $fromTemplate = UserHabitItem::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($fromTemplate);
        $this->assertSame($template->id, $fromTemplate->template_habit_item_id);
        $this->assertSame('Uống nước', $fromTemplate->name);

        // Custom — not in admin catalog
        $this->actingAs($user, 'web')
            ->post(route('habits.items.store'), [
                'mode' => 'custom',
                'name' => 'Dậy lúc 5h',
                'icon' => '⏰',
            ])
            ->assertRedirect(route('habits.items'));

        $custom = UserHabitItem::query()->where('name', 'Dậy lúc 5h')->first();
        $this->assertNotNull($custom);
        $this->assertNull($custom->template_habit_item_id);
        $this->assertDatabaseMissing('habit_items', ['name' => 'Dậy lúc 5h']);
        $this->assertSame(1, HabitItem::query()->count());
    }

    public function test_cannot_adopt_same_template_twice(): void
    {
        $this->withoutVite();
        $user = User::factory()->create();
        $template = $this->makeTemplate();
        app(HabitService::class)->adoptTemplate($user, $template->id);

        $this->actingAs($user, 'web')
            ->from(route('habits.items'))
            ->post(route('habits.items.store'), [
                'mode' => 'template',
                'template_habit_item_id' => $template->id,
            ])
            ->assertRedirect(route('habits.items'))
            ->assertSessionHasErrors('template_habit_item_id');
    }

    public function test_grid_only_shows_user_own_active_items(): void
    {
        $this->withoutVite();
        Carbon::setTestNow(Carbon::parse('2026-07-11 10:00:00', config('app.timezone')));

        $user = User::factory()->create();
        $other = User::factory()->create();
        $tpl = $this->makeTemplate();

        $mine = app(HabitService::class)->createCustom($user, ['name' => 'Của tôi']);
        app(HabitService::class)->createCustom($other, ['name' => 'Của người khác']);
        app(HabitService::class)->adoptTemplate($user, $tpl->id);

        $this->actingAs($user, 'web')
            ->get(route('habits.index', ['year' => 2026, 'month' => 7]))
            ->assertOk()
            ->assertSee('Của tôi', false)
            ->assertDontSee('Của người khác', false);

        $grid = app(HabitService::class)->monthGrid($user, 2026, 7);
        $this->assertCount(2, $grid['items']);

        Carbon::setTestNow();
    }

    public function test_cycle_cell_null_done_missed_null(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-11 10:00:00', config('app.timezone')));

        $user = User::factory()->create();
        $item = app(HabitService::class)->createCustom($user, ['name' => 'Test']);
        $service = app(HabitService::class);

        $r1 = $service->cycleCell($user, $item->id, '2026-07-10');
        $this->assertSame('done', $r1['status']);

        $r2 = $service->cycleCell($user, $item->id, '2026-07-10');
        $this->assertSame('missed', $r2['status']);
        $this->assertSame(HabitEntryStatus::Missed, HabitEntry::query()->first()->status);

        $r3 = $service->cycleCell($user, $item->id, '2026-07-10');
        $this->assertNull($r3['status']);
        $this->assertSame(0, HabitEntry::query()->count());
        $this->assertSame(3, HabitEntryHistory::query()->where('user_id', $user->id)->count());

        Carbon::setTestNow();
    }

    public function test_web_cycle_json_uses_user_habit_item_id(): void
    {
        $this->withoutVite();
        Carbon::setTestNow(Carbon::parse('2026-07-11 10:00:00', config('app.timezone')));

        $user = User::factory()->create();
        $item = app(HabitService::class)->createCustom($user, ['name' => 'X']);

        $this->actingAs($user, 'web')
            ->postJson(route('habits.cycle'), [
                'user_habit_item_id' => $item->id,
                'date' => '2026-07-11',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'done');

        Carbon::setTestNow();
    }

    public function test_user_cannot_cycle_others_item(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-11 10:00:00', config('app.timezone')));

        $owner = User::factory()->create();
        $other = User::factory()->create();
        $item = app(HabitService::class)->createCustom($owner, ['name' => 'Private']);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(HabitService::class)->cycleCell($other, $item->id, '2026-07-11');
    }

    public function test_user_can_rename_and_delete_item(): void
    {
        $this->withoutVite();
        $user = User::factory()->create();
        $item = app(HabitService::class)->createCustom($user, ['name' => 'Cũ']);

        $this->actingAs($user, 'web')
            ->put(route('habits.items.update', $item), [
                'name' => 'Mới',
                'is_active' => 1,
            ])
            ->assertRedirect(route('habits.items'));

        $this->assertSame('Mới', $item->fresh()->name);

        $this->actingAs($user, 'web')
            ->delete(route('habits.items.destroy', $item))
            ->assertRedirect(route('habits.items'));

        $this->assertDatabaseMissing('user_habit_items', ['id' => $item->id]);
    }

    public function test_history_page(): void
    {
        $this->withoutVite();
        Carbon::setTestNow(Carbon::parse('2026-07-11 10:00:00', config('app.timezone')));

        $user = User::factory()->create();
        $item = app(HabitService::class)->createCustom($user, ['name' => 'Đọc sách']);
        app(HabitService::class)->cycleCell($user, $item->id, '2026-07-11');

        $this->actingAs($user, 'web')
            ->get(route('habits.history'))
            ->assertOk()
            ->assertSee('Đọc sách', false);

        Carbon::setTestNow();
    }

    public function test_api_items_and_grid(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-11 10:00:00', config('app.timezone')));

        $user = User::factory()->create();
        $template = $this->makeTemplate();

        $this->actingAs($user, 'web')
            ->postJson('/api/habits/items', [
                'mode' => 'custom',
                'name' => 'API custom',
            ])
            ->assertCreated()
            ->assertJsonPath('data.is_custom', true);

        $this->actingAs($user, 'web')
            ->postJson('/api/habits/items', [
                'mode' => 'template',
                'template_habit_item_id' => $template->id,
            ])
            ->assertCreated();

        $this->actingAs($user, 'web')
            ->getJson('/api/habits/items')
            ->assertOk()
            ->assertJsonPath('data.items.0.name', 'API custom');

        $itemId = UserHabitItem::query()->where('name', 'API custom')->value('id');

        $this->actingAs($user, 'web')
            ->postJson('/api/habits/cycle', [
                'user_habit_item_id' => $itemId,
                'date' => '2026-07-11',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'done');

        Carbon::setTestNow();
    }

    public function test_admin_templates_still_crud(): void
    {
        $admin = Admin::factory()->create();
        Sanctum::actingAs($admin, ['*'], 'admin');

        $this->postJson('/api/admin/habit-items', [
            'name' => 'Meditation',
            'icon' => '🧘',
        ])->assertCreated();

        $this->assertDatabaseHas('habit_items', ['name' => 'Meditation']);
    }

    public function test_custom_item_never_written_to_admin_catalog(): void
    {
        $user = User::factory()->create();
        app(HabitService::class)->createCustom($user, [
            'name' => 'Habit bí mật cá nhân',
            'icon' => '🔒',
        ]);

        $this->assertSame(0, HabitItem::query()->where('name', 'Habit bí mật cá nhân')->count());
        $this->assertSame(1, UserHabitItem::query()->where('name', 'Habit bí mật cá nhân')->count());
    }

    public function test_items_page_shows_icon_picker(): void
    {
        $this->withoutVite();
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->get(route('habits.items'))
            ->assertOk()
            ->assertSee('Chọn icon', false)
            ->assertSee('💪', false)
            ->assertSee('📚', false);
    }

    public function test_create_custom_rejects_invalid_icon(): void
    {
        $this->withoutVite();
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->from(route('habits.items'))
            ->post(route('habits.items.store'), [
                'mode' => 'custom',
                'name' => 'Test',
                'icon' => 'not-an-allowed-icon',
            ])
            ->assertRedirect(route('habits.items'))
            ->assertSessionHasErrors('icon');
    }

    public function test_create_custom_accepts_preset_icon(): void
    {
        $this->withoutVite();
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->post(route('habits.items.store'), [
                'mode' => 'custom',
                'name' => 'Đọc sách',
                'icon' => '📚',
            ])
            ->assertRedirect(route('habits.items'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('user_habit_items', [
            'user_id' => $user->id,
            'name' => 'Đọc sách',
            'icon' => '📚',
        ]);
    }

    public function test_habits_page_shows_chart_sections(): void
    {
        $this->withoutVite();
        Carbon::setTestNow(Carbon::parse('2026-07-11 10:00:00', config('app.timezone')));

        $user = User::factory()->create();
        $item = app(HabitService::class)->createCustom($user, ['name' => 'Chart item', 'icon' => '🎯']);
        app(HabitService::class)->cycleCell($user, $item->id, '2026-07-11');

        $this->actingAs($user, 'web')
            ->get(route('habits.index', ['year' => 2026, 'month' => 7]))
            ->assertOk()
            ->assertSee('Cơ cấu tháng này', false)
            ->assertSee('Hiệu suất theo đầu mục', false);

        Carbon::setTestNow();
    }

    public function test_home_shows_overview_charts(): void
    {
        $this->withoutVite();
        Carbon::setTestNow(Carbon::parse('2026-07-11 10:00:00', config('app.timezone')));

        $user = User::factory()->create();
        $item = app(HabitService::class)->createCustom($user, ['name' => 'Home chart', 'icon' => '💪']);
        app(HabitService::class)->cycleCell($user, $item->id, '2026-07-11');

        $this->actingAs($user, 'web')
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Tổng quan Habit', false)
            ->assertSee('7 ngày gần nhất', false)
            ->assertSee('Tháng này & top đầu mục', false);

        Carbon::setTestNow();
    }

    public function test_overview_charts_structure(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-11 10:00:00', config('app.timezone')));
        $user = User::factory()->create();
        $item = app(HabitService::class)->createCustom($user, ['name' => 'A']);
        app(HabitService::class)->cycleCell($user, $item->id, '2026-07-10');

        $charts = app(HabitService::class)->overviewCharts($user);
        $this->assertCount(7, $charts['last_7_days']);
        $this->assertArrayHasKey('done', $charts['month']);
        $this->assertNotEmpty($charts['top_items']);
        $this->assertSame('A', $charts['top_items'][0]['name']);

        Carbon::setTestNow();
    }

    public function test_first_visit_gets_starter_samples(): void
    {
        $this->withoutVite();
        // Ensure admin templates exist
        foreach (['Tập thể dục', 'Uống đủ nước'] as $i => $name) {
            HabitItem::query()->create([
                'name' => $name,
                'slug' => \Illuminate\Support\Str::slug($name).'-'.$i,
                'icon' => '💪',
                'is_active' => true,
                'sort_order' => $i,
            ]);
        }

        $user = User::factory()->create();
        $this->assertSame(0, UserHabitItem::query()->where('user_id', $user->id)->count());

        $this->actingAs($user, 'web')
            ->get(route('habits.index'))
            ->assertOk()
            ->assertSee('Tập thể dục', false);

        $this->assertGreaterThanOrEqual(2, UserHabitItem::query()->where('user_id', $user->id)->count());
    }

    public function test_user_can_delete_starter_sample(): void
    {
        $this->withoutVite();
        $user = User::factory()->create();
        HabitItem::query()->create([
            'name' => 'Đọc sách',
            'slug' => 'doc-sach-starter',
            'icon' => '📚',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->habitServiceEnsure($user);
        $item = UserHabitItem::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($item);

        $this->actingAs($user, 'web')
            ->delete(route('habits.items.destroy', $item))
            ->assertRedirect(route('habits.items'));

        $this->assertDatabaseMissing('user_habit_items', ['id' => $item->id]);
    }

    private function habitServiceEnsure(User $user): void
    {
        app(HabitService::class)->ensureStarterItems($user);
    }

    public function test_admin_can_view_user_habit_history(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-11 10:00:00', config('app.timezone')));

        $user = User::factory()->create(['username' => 'habit_user']);
        $item = app(HabitService::class)->createCustom($user, ['name' => 'Tập gym', 'icon' => '💪']);
        app(HabitService::class)->cycleCell($user, $item->id, '2026-07-11');

        $admin = Admin::factory()->create();
        Sanctum::actingAs($admin, ['*'], 'admin');

        $this->getJson('/api/admin/users/'.$user->id.'/habits/summary')
            ->assertOk()
            ->assertJsonPath('data.user.username', 'habit_user')
            ->assertJsonPath('data.items_count', 1)
            ->assertJsonPath('data.items.0.name', 'Tập gym')
            ->assertJsonPath('data.month.done', 1);

        $this->getJson('/api/admin/users/'.$user->id.'/habits/history')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.to_status', 'done')
            ->assertJsonPath('data.0.habit_item.name', 'Tập gym');

        Carbon::setTestNow();
    }

    public function test_month_year_picker_past_15_years_no_future(): void
    {
        $this->withoutVite();
        Carbon::setTestNow(Carbon::parse('2026-07-11 10:00:00', config('app.timezone')));

        $user = User::factory()->create();
        app(HabitService::class)->createCustom($user, ['name' => 'Nav']);

        // Valid: within last 15 years
        $this->actingAs($user, 'web')
            ->get(route('habits.index', ['year' => 2015, 'month' => 3]))
            ->assertOk()
            ->assertSee('2015', false);

        // Edge of range: currentYear - 15
        $this->actingAs($user, 'web')
            ->get(route('habits.index', ['year' => 2011, 'month' => 1]))
            ->assertOk()
            ->assertSee('2011', false);

        // Too far past → clamp to current
        $this->actingAs($user, 'web')
            ->get(route('habits.index', ['year' => 2000, 'month' => 1]))
            ->assertOk()
            ->assertSee('2026', false);

        // Future year → clamp to current
        $this->actingAs($user, 'web')
            ->get(route('habits.index', ['year' => 2030, 'month' => 3]))
            ->assertOk()
            ->assertSee('2026', false);

        // Future month in current year → clamp to current month
        $response = $this->actingAs($user, 'web')
            ->get(route('habits.index', ['year' => 2026, 'month' => 12]))
            ->assertOk();
        $response->assertSee('value="7"', false);
        $response->assertSee('selected', false);
        // Selected month option should be July (current under test now)
        $this->assertMatchesRegularExpression(
            '/<option value="7"[^>]*selected/',
            $response->getContent()
        );

        Carbon::setTestNow();
    }
}
