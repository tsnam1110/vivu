<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\HabitEntryStatus;
use App\Models\HabitEntry;
use App\Models\HabitEntryHistory;
use App\Models\HabitItem;
use App\Models\User;
use App\Models\UserHabitItem;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HabitService
{
    /**
     * Built-in starter samples when admin catalog is empty.
     * Copied into user_habit_items as custom rows (not written to habit_items).
     *
     * @var list<array{name: string, icon: string, description: string}>
     */
    public const STARTER_SAMPLES = [
        ['name' => 'Tập thể dục', 'icon' => '💪', 'description' => 'Vận động / gym / yoga'],
        ['name' => 'Uống đủ nước', 'icon' => '💧', 'description' => 'Hydrate trong ngày'],
        ['name' => 'Đọc sách', 'icon' => '📚', 'description' => 'Đọc ít nhất 15 phút'],
        ['name' => 'Ghi trải nghiệm', 'icon' => '📝', 'description' => 'Lưu 1 trải nghiệm trên ViVu'],
        ['name' => 'Ăn uống lành mạnh', 'icon' => '🥗', 'description' => 'Bữa ăn cân bằng'],
        ['name' => 'Ngủ đủ giấc', 'icon' => '😴', 'description' => 'Ngủ trước 23h / đủ 7h'],
    ];

    /**
     * Active personal rows for the user (grid rows).
     *
     * @return Collection<int, UserHabitItem>
     */
    public function activeItemsFor(User $user): Collection
    {
        return UserHabitItem::query()
            ->forUser($user)
            ->active()
            ->get();
    }

    /**
     * First visit: if user has no personal items, copy starter samples (admin templates or built-in).
     * Does nothing if user already has any item (including after they deleted some but kept others).
     *
     * @return int Number of starter rows created
     */
    public function ensureStarterItems(User $user): int
    {
        if (UserHabitItem::query()->forUser($user)->exists()) {
            return 0;
        }

        return $this->applyStarterTemplates($user);
    }

    /**
     * Add starter samples the user does not already have (from admin templates, or built-in fallback).
     * Safe to call when empty or to "restore samples" after deleting some.
     *
     * @return int Number of rows created
     */
    public function applyStarterTemplates(User $user): int
    {
        return (int) DB::transaction(function () use ($user) {
            $created = 0;
            $templates = HabitItem::query()->active()->orderBy('sort_order')->orderBy('id')->get();

            if ($templates->isNotEmpty()) {
                foreach ($templates as $template) {
                    $exists = UserHabitItem::query()
                        ->forUser($user)
                        ->where('template_habit_item_id', $template->id)
                        ->exists();
                    if ($exists) {
                        continue;
                    }
                    $maxOrder = (int) UserHabitItem::query()->forUser($user)->max('sort_order');
                    UserHabitItem::query()->create([
                        'user_id' => $user->id,
                        'template_habit_item_id' => $template->id,
                        'name' => $template->name,
                        'description' => $template->description,
                        'icon' => $template->icon ?: '✨',
                        'sort_order' => $maxOrder + 1,
                        'is_active' => true,
                    ]);
                    $created++;
                }

                return $created;
            }

            // No admin catalog: create built-in samples as custom personal rows.
            foreach (self::STARTER_SAMPLES as $sample) {
                $exists = UserHabitItem::query()
                    ->forUser($user)
                    ->whereNull('template_habit_item_id')
                    ->where('name', $sample['name'])
                    ->exists();
                if ($exists) {
                    continue;
                }
                $maxOrder = (int) UserHabitItem::query()->forUser($user)->max('sort_order');
                UserHabitItem::query()->create([
                    'user_id' => $user->id,
                    'template_habit_item_id' => null,
                    'name' => $sample['name'],
                    'description' => $sample['description'],
                    'icon' => $sample['icon'],
                    'sort_order' => $maxOrder + 1,
                    'is_active' => true,
                ]);
                $created++;
            }

            return $created;
        });
    }

    /**
     * Admin templates not yet adopted by this user.
     *
     * @return Collection<int, HabitItem>
     */
    public function availableTemplatesFor(User $user): Collection
    {
        $usedTemplateIds = UserHabitItem::query()
            ->forUser($user)
            ->whereNotNull('template_habit_item_id')
            ->pluck('template_habit_item_id');

        return HabitItem::query()
            ->active()
            ->when($usedTemplateIds->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $usedTemplateIds))
            ->get();
    }

    /**
     * Adopt an admin template as a personal row (copy name/icon — not shared row).
     */
    public function adoptTemplate(User $user, int $templateId): UserHabitItem
    {
        $template = HabitItem::query()->where('is_active', true)->find($templateId);
        if ($template === null) {
            throw ValidationException::withMessages([
                'template_habit_item_id' => 'Mẫu không hợp lệ hoặc đã tắt.',
            ]);
        }

        $exists = UserHabitItem::query()
            ->forUser($user)
            ->where('template_habit_item_id', $template->id)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'template_habit_item_id' => 'Bạn đã thêm mẫu này rồi.',
            ]);
        }

        $maxOrder = (int) UserHabitItem::query()->forUser($user)->max('sort_order');

        return UserHabitItem::query()->create([
            'user_id' => $user->id,
            'template_habit_item_id' => $template->id,
            'name' => $template->name,
            'description' => $template->description,
            'icon' => $template->icon,
            'sort_order' => $maxOrder + 1,
            'is_active' => true,
        ]);
    }

    /**
     * Create fully custom personal item (not stored in admin catalog).
     *
     * @param  array{name: string, description?: string|null, icon?: string|null}  $data
     */
    public function createCustom(User $user, array $data): UserHabitItem
    {
        $name = trim((string) ($data['name'] ?? ''));
        if ($name === '') {
            throw ValidationException::withMessages([
                'name' => 'Tên đầu mục là bắt buộc.',
            ]);
        }

        $maxOrder = (int) UserHabitItem::query()->forUser($user)->max('sort_order');

        return UserHabitItem::query()->create([
            'user_id' => $user->id,
            'template_habit_item_id' => null,
            'name' => mb_substr($name, 0, 120),
            'description' => isset($data['description']) ? mb_substr((string) $data['description'], 0, 500) : null,
            'icon' => isset($data['icon']) && in_array((string) $data['icon'], UserHabitItem::ICONS, true)
                ? (string) $data['icon']
                : '✨',
            'sort_order' => $maxOrder + 1,
            'is_active' => true,
        ]);
    }

    /**
     * @param  array{name?: string, description?: string|null, icon?: string|null, is_active?: bool, sort_order?: int}  $data
     */
    public function updateUserItem(User $user, UserHabitItem $item, array $data): UserHabitItem
    {
        $this->assertOwns($user, $item);

        $payload = collect($data)->only(['name', 'description', 'icon', 'is_active', 'sort_order'])->all();
        if (isset($payload['name'])) {
            $payload['name'] = mb_substr(trim((string) $payload['name']), 0, 120);
            if ($payload['name'] === '') {
                throw ValidationException::withMessages(['name' => 'Tên đầu mục là bắt buộc.']);
            }
        }

        $item->fill($payload);
        $item->save();

        return $item->fresh() ?? $item;
    }

    public function deleteUserItem(User $user, UserHabitItem $item): void
    {
        $this->assertOwns($user, $item);
        $item->delete(); // cascades entries + histories
    }

    /**
     * @return array{
     *   year: int,
     *   month: int,
     *   days: list<array{date: string, day: int, is_future: bool, is_today: bool, weekday: int}>,
     *   items: list<array{id: int, name: string, icon: ?string, description: ?string, is_custom: bool}>,
     *   cells: array<string, array<string, string|null>>,
     *   stats: array{done: int, missed: int, empty: int, total_cells: int}
     * }
     */
    public function monthGrid(User $user, int $year, int $month): array
    {
        $start = Carbon::create($year, $month, 1, 0, 0, 0, config('app.timezone'))->startOfDay();
        $end = $start->copy()->endOfMonth()->startOfDay();
        $today = $this->today();

        $days = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $days[] = [
                'date' => $cursor->toDateString(),
                'day' => $cursor->day,
                'is_future' => $cursor->gt($today),
                'is_today' => $cursor->equalTo($today),
                'weekday' => $cursor->dayOfWeek,
            ];
            $cursor->addDay();
        }

        $items = $this->activeItemsFor($user);
        $entries = HabitEntry::query()
            ->where('user_id', $user->id)
            ->whereIn('user_habit_item_id', $items->pluck('id'))
            ->whereDate('entry_date', '>=', $start->toDateString())
            ->whereDate('entry_date', '<=', $end->toDateString())
            ->get();

        /** @var array<string, array<string, string|null>> $cells */
        $cells = [];
        foreach ($items as $item) {
            $cells[(string) $item->id] = array_fill_keys(array_column($days, 'date'), null);
        }
        foreach ($entries as $entry) {
            $itemKey = (string) $entry->user_habit_item_id;
            $dateKey = $entry->entry_date->toDateString();
            if (isset($cells[$itemKey]) && array_key_exists($dateKey, $cells[$itemKey])) {
                $cells[$itemKey][$dateKey] = $entry->status->value;
            }
        }

        $done = 0;
        $missed = 0;
        $empty = 0;
        foreach ($cells as $row) {
            foreach ($row as $status) {
                match ($status) {
                    HabitEntryStatus::Done->value => $done++,
                    HabitEntryStatus::Missed->value => $missed++,
                    default => $empty++,
                };
            }
        }

        return [
            'year' => $year,
            'month' => $month,
            'days' => $days,
            'items' => $items->map(fn (UserHabitItem $i) => [
                'id' => $i->id,
                'name' => $i->name,
                'icon' => $i->icon,
                'description' => $i->description,
                'is_custom' => $i->isCustom(),
            ])->values()->all(),
            'cells' => $cells,
            'stats' => [
                'done' => $done,
                'missed' => $missed,
                'empty' => $empty,
                'total_cells' => $done + $missed + $empty,
            ],
        ];
    }

    /**
     * @return array{status: string|null, from_status: string|null, to_status: string|null, user_habit_item_id: int, date: string}
     */
    public function cycleCell(
        User $user,
        int $userHabitItemId,
        CarbonInterface|string $date,
        string $source = 'web',
    ): array {
        $day = $this->normalizeDate($date);
        $today = $this->today();

        if ($day->gt($today)) {
            throw ValidationException::withMessages([
                'date' => 'Không thể ghi nhận ngày trong tương lai.',
            ]);
        }

        $item = UserHabitItem::query()
            ->forUser($user)
            ->where('is_active', true)
            ->find($userHabitItemId);

        if ($item === null) {
            throw ValidationException::withMessages([
                'user_habit_item_id' => 'Đầu mục không hợp lệ hoặc đã tắt.',
            ]);
        }

        return DB::transaction(function () use ($user, $item, $day, $source) {
            $entry = HabitEntry::query()
                ->where('user_id', $user->id)
                ->where('user_habit_item_id', $item->id)
                ->whereDate('entry_date', $day->toDateString())
                ->lockForUpdate()
                ->first();

            $from = $entry?->status;
            $to = HabitEntryStatus::next($from);

            if ($to === null) {
                $entry?->delete();
            } elseif ($entry) {
                $entry->status = $to;
                $entry->save();
            } else {
                HabitEntry::query()->create([
                    'user_id' => $user->id,
                    'user_habit_item_id' => $item->id,
                    'entry_date' => $day->toDateString(),
                    'status' => $to,
                ]);
            }

            HabitEntryHistory::query()->create([
                'user_id' => $user->id,
                'user_habit_item_id' => $item->id,
                'entry_date' => $day->toDateString(),
                'from_status' => $from?->value,
                'to_status' => $to?->value,
                'source' => $source,
                'changed_at' => now(),
            ]);

            return [
                'status' => $to?->value,
                'from_status' => $from?->value,
                'to_status' => $to?->value,
                'user_habit_item_id' => $item->id,
                'date' => $day->toDateString(),
            ];
        });
    }

    /**
     * @return LengthAwarePaginator<int, HabitEntryHistory>
     */
    public function historyForUser(User $user, int $perPage = 30): LengthAwarePaginator
    {
        return HabitEntryHistory::query()
            ->where('user_id', $user->id)
            ->with('userHabitItem')
            ->orderByDesc('changed_at')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * @return array{done: int, missed: int, empty: int, items_count: int, month_label: string, rate: float}
     */
    public function currentMonthSummary(User $user): array
    {
        $today = $this->today();
        $grid = $this->monthGrid($user, (int) $today->year, (int) $today->month);
        $stats = $grid['stats'];
        $filled = $stats['done'] + $stats['missed'];
        $rate = $filled > 0 ? round($stats['done'] / $filled, 4) : 0.0;

        return [
            'done' => $stats['done'],
            'missed' => $stats['missed'],
            'empty' => $stats['empty'],
            'items_count' => count($grid['items']),
            'month_label' => $today->format('m/Y'),
            'rate' => $rate,
        ];
    }

    /**
     * Overview charts for personal vault home.
     *
     * @return array{
     *   last_7_days: list<array{date: string, label: string, done: int, missed: int}>,
     *   month: array{done: int, missed: int, empty: int, rate: float, month_label: string, items_count: int},
     *   top_items: list<array{id: int, name: string, icon: ?string, done: int, filled: int, rate: float}>
     * }
     */
    public function overviewCharts(User $user): array
    {
        $today = $this->today();
        $grid = $this->monthGrid($user, (int) $today->year, (int) $today->month);
        $summary = $this->currentMonthSummary($user);

        // Last 7 days (including today): count done/missed across all active items.
        $last7 = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = $today->copy()->subDays($i);
            $date = $day->toDateString();
            $done = 0;
            $missed = 0;
            foreach ($grid['cells'] as $row) {
                $status = $row[$date] ?? null;
                if ($status === HabitEntryStatus::Done->value) {
                    $done++;
                } elseif ($status === HabitEntryStatus::Missed->value) {
                    $missed++;
                }
            }
            // If day not in current month grid (edge of month), load from DB quickly.
            if (! isset($grid['days'][0]) || $date < $grid['days'][0]['date'] || $date > end($grid['days'])['date']) {
                $counts = $this->countsForUserOnDate($user, $date);
                $done = $counts['done'];
                $missed = $counts['missed'];
            }
            $weekday = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'][$day->dayOfWeek];
            $last7[] = [
                'date' => $date,
                'label' => $weekday.' '.$day->format('d'),
                'done' => $done,
                'missed' => $missed,
            ];
        }

        $topItems = [];
        foreach ($grid['items'] as $item) {
            $id = (string) $item['id'];
            $row = $grid['cells'][$id] ?? [];
            $done = 0;
            $missed = 0;
            foreach ($row as $status) {
                if ($status === HabitEntryStatus::Done->value) {
                    $done++;
                } elseif ($status === HabitEntryStatus::Missed->value) {
                    $missed++;
                }
            }
            $filled = $done + $missed;
            $topItems[] = [
                'id' => $item['id'],
                'name' => $item['name'],
                'icon' => $item['icon'],
                'done' => $done,
                'filled' => $filled,
                'rate' => $filled > 0 ? round($done / $filled, 4) : 0.0,
            ];
        }
        usort($topItems, fn ($a, $b) => $b['rate'] <=> $a['rate'] ?: $b['done'] <=> $a['done']);
        $topItems = array_slice($topItems, 0, 5);

        return [
            'last_7_days' => $last7,
            'month' => [
                'done' => $summary['done'],
                'missed' => $summary['missed'],
                'empty' => $summary['empty'],
                'rate' => $summary['rate'],
                'month_label' => $summary['month_label'],
                'items_count' => $summary['items_count'],
            ],
            'top_items' => $topItems,
        ];
    }

    /**
     * @return array{done: int, missed: int}
     */
    private function countsForUserOnDate(User $user, string $date): array
    {
        $itemIds = $this->activeItemsFor($user)->pluck('id');
        if ($itemIds->isEmpty()) {
            return ['done' => 0, 'missed' => 0];
        }

        $rows = HabitEntry::query()
            ->where('user_id', $user->id)
            ->whereIn('user_habit_item_id', $itemIds)
            ->whereDate('entry_date', $date)
            ->get(['status']);

        $done = 0;
        $missed = 0;
        foreach ($rows as $row) {
            if ($row->status === HabitEntryStatus::Done) {
                $done++;
            } else {
                $missed++;
            }
        }

        return ['done' => $done, 'missed' => $missed];
    }

    public function today(): Carbon
    {
        return now(config('app.timezone'))->startOfDay();
    }

    private function assertOwns(User $user, UserHabitItem $item): void
    {
        if ($item->user_id !== $user->id) {
            abort(403);
        }
    }

    private function normalizeDate(CarbonInterface|string $date): Carbon
    {
        if (is_string($date)) {
            return Carbon::parse($date, config('app.timezone'))->startOfDay();
        }

        return Carbon::parse($date->toDateString(), config('app.timezone'))->startOfDay();
    }
}
