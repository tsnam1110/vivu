<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\CycleHabitCellRequest;
use App\Http\Requests\Web\StoreUserHabitItemRequest;
use App\Http\Requests\Web\UpdateUserHabitItemRequest;
use App\Models\UserHabitItem;
use App\Services\HabitService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HabitController extends Controller
{
    public function __construct(private readonly HabitService $habitService) {}

    public function index(Request $request): View
    {
        $user = $request->user('web');
        abort_unless($user && $user->isActive(), 403);

        // First open: gán sẵn các mẫu mặc định (user có thể xoá ở trang Đầu mục).
        $this->habitService->ensureStarterItems($user);

        $today = $this->habitService->today();
        // Past only: from (today − 15 years) through current month — no future.
        $minYear = (int) $today->year - 15;
        $maxYear = (int) $today->year;
        $currentMonthStart = $today->copy()->startOfMonth();

        $year = (int) $request->query('year', $today->year);
        $month = (int) $request->query('month', $today->month);

        if ($month < 1 || $month > 12) {
            $month = (int) $today->month;
        }
        if ($year < $minYear || $year > $maxYear) {
            $year = (int) $today->year;
            $month = (int) $today->month;
        }

        $cursor = Carbon::create($year, $month, 1, 0, 0, 0, config('app.timezone'))->startOfMonth();
        // Clamp future months back to current month.
        if ($cursor->gt($currentMonthStart)) {
            $year = (int) $today->year;
            $month = (int) $today->month;
            $cursor = $currentMonthStart->copy();
        }

        $grid = $this->habitService->monthGrid($user, $year, $month);
        $minCursor = Carbon::create($minYear, 1, 1, 0, 0, 0, config('app.timezone'))->startOfMonth();
        $prev = $cursor->copy()->subMonth();
        $next = $cursor->copy()->addMonth();

        // Years: oldest → newest (newest = current year only).
        $years = range($minYear, $maxYear);
        // Month dropdown: always list months allowed for the *selected* year
        // (full 1–12 for past years; only up to current month for this year).
        $months = [];
        $maxMonthForYear = $year === $maxYear ? (int) $today->month : 12;
        for ($m = 1; $m <= $maxMonthForYear; $m++) {
            $months[$m] = 'Tháng '.$m;
        }

        return view('habits.index', [
            'grid' => $grid,
            'year' => $year,
            'month' => $month,
            'monthLabel' => $this->vietnameseMonthLabel($cursor),
            'prevYear' => (int) $prev->year,
            'prevMonth' => (int) $prev->month,
            'nextYear' => (int) $next->year,
            'nextMonth' => (int) $next->month,
            'canGoPrev' => $prev->gte($minCursor),
            'canGoNext' => $next->lte($currentMonthStart),
            'minYear' => $minYear,
            'maxYear' => $maxYear,
            'years' => $years,
            'months' => $months,
            'weekdayLabels' => ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'],
            'itemCount' => count($grid['items']),
        ]);
    }

    public function items(Request $request): View
    {
        $user = $request->user('web');
        abort_unless($user && $user->isActive(), 403);

        $this->habitService->ensureStarterItems($user);

        $myItems = UserHabitItem::query()
            ->forUser($user)
            ->orderByDesc('is_active')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $templates = $this->habitService->availableTemplatesFor($user);
        $canRestoreStarters = $templates->isNotEmpty()
            || UserHabitItem::query()->forUser($user)->count() === 0;

        return view('habits.items', [
            'myItems' => $myItems,
            'templates' => $templates,
            'icons' => UserHabitItem::ICONS,
            'canRestoreStarters' => $canRestoreStarters || $templates->isNotEmpty(),
        ]);
    }

    public function storeItem(StoreUserHabitItemRequest $request): RedirectResponse
    {
        $user = $request->user('web');
        $data = $request->validated();

        if ($data['mode'] === 'starters') {
            $n = $this->habitService->applyStarterTemplates($user);
            $message = $n > 0
                ? __('messages.habit_starters_restored', ['count' => $n])
                : __('messages.habit_starters_already');
        } elseif ($data['mode'] === 'template') {
            $this->habitService->adoptTemplate($user, (int) $data['template_habit_item_id']);
            $message = __('messages.habit_item_added_from_template');
        } else {
            $this->habitService->createCustom($user, $data);
            $message = __('messages.habit_item_created_custom');
        }

        return redirect()
            ->route('habits.items')
            ->with('success', $message);
    }

    public function updateItem(UpdateUserHabitItemRequest $request, UserHabitItem $userHabitItem): RedirectResponse
    {
        $this->habitService->updateUserItem(
            $request->user('web'),
            $userHabitItem,
            $request->validated(),
        );

        return redirect()
            ->route('habits.items')
            ->with('success', __('messages.habit_item_updated'));
    }

    public function destroyItem(Request $request, UserHabitItem $userHabitItem): RedirectResponse
    {
        $user = $request->user('web');
        abort_unless($user && $user->isActive(), 403);
        abort_unless($userHabitItem->user_id === $user->id, 403);

        $this->habitService->deleteUserItem($user, $userHabitItem);

        return redirect()
            ->route('habits.items')
            ->with('success', __('messages.habit_item_deleted'));
    }

    public function history(Request $request): View
    {
        $user = $request->user('web');
        abort_unless($user && $user->isActive(), 403);

        $histories = $this->habitService->historyForUser($user, 40);

        return view('habits.history', [
            'histories' => $histories,
        ]);
    }

    public function cycle(CycleHabitCellRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->habitService->cycleCell(
            $request->user('web'),
            (int) $request->validated('user_habit_item_id'),
            $request->validated('date'),
            'web',
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['data' => $result]);
        }

        return redirect()
            ->back()
            ->with('success', __('messages.habit_cell_updated'));
    }

    private function vietnameseMonthLabel(Carbon $month): string
    {
        return 'Tháng '.$month->format('n').'/'.$month->format('Y');
    }
}
