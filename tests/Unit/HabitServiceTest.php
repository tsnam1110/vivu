<?php

namespace Tests\Unit;

use App\Enums\HabitEntryStatus;
use App\Models\HabitItem;
use App\Models\User;
use App\Services\HabitService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HabitServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_cycle_order(): void
    {
        $this->assertSame(HabitEntryStatus::Done, HabitEntryStatus::next(null));
        $this->assertSame(HabitEntryStatus::Missed, HabitEntryStatus::next(HabitEntryStatus::Done));
        $this->assertNull(HabitEntryStatus::next(HabitEntryStatus::Missed));
    }

    public function test_month_grid_uses_user_items_only(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-15', config('app.timezone')));

        $user = User::factory()->create();
        HabitItem::query()->create([
            'name' => 'Template only',
            'slug' => 'template-only',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $service = app(HabitService::class);
        $item = $service->createCustom($user, ['name' => 'Mine']);
        $service->cycleCell($user, $item->id, '2026-07-01');

        $grid = $service->monthGrid($user, 2026, 7);
        $this->assertCount(1, $grid['items']);
        $this->assertSame('Mine', $grid['items'][0]['name']);
        $this->assertSame(1, $grid['stats']['done']);

        Carbon::setTestNow();
    }

    public function test_available_templates_exclude_adopted(): void
    {
        $user = User::factory()->create();
        $a = HabitItem::query()->create(['name' => 'A', 'slug' => 'a', 'is_active' => true, 'sort_order' => 1]);
        $b = HabitItem::query()->create(['name' => 'B', 'slug' => 'b', 'is_active' => true, 'sort_order' => 2]);

        $service = app(HabitService::class);
        $service->adoptTemplate($user, $a->id);

        $available = $service->availableTemplatesFor($user);
        $this->assertCount(1, $available);
        $this->assertSame($b->id, $available->first()->id);
    }
}
