<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HabitEntryHistory;
use App\Models\User;
use App\Models\UserHabitItem;
use App\Services\HabitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Admin read-only view of a user's habit items + entry history.
 */
class UserHabitController extends Controller
{
    public function __construct(private readonly HabitService $habitService) {}

    public function summary(User $user): JsonResponse
    {
        $items = UserHabitItem::query()
            ->forUser($user)
            ->orderByDesc('is_active')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $summary = $this->habitService->currentMonthSummary($user);

        return response()->json([
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                ],
                'month' => $summary,
                'items' => $items->map(fn (UserHabitItem $i) => [
                    'id' => $i->id,
                    'name' => $i->name,
                    'icon' => $i->icon,
                    'description' => $i->description,
                    'is_active' => $i->is_active,
                    'is_custom' => $i->isCustom(),
                    'template_habit_item_id' => $i->template_habit_item_id,
                    'sort_order' => $i->sort_order,
                    'created_at' => $i->created_at?->toIso8601String(),
                ]),
                'items_count' => $items->count(),
                'active_items_count' => $items->where('is_active', true)->count(),
            ],
        ]);
    }

    public function history(Request $request, User $user): JsonResponse
    {
        $perPage = min(50, max(1, (int) $request->integer('per_page', 20)));

        $paginator = HabitEntryHistory::query()
            ->where('user_id', $user->id)
            ->with('userHabitItem')
            ->orderByDesc('changed_at')
            ->orderByDesc('id')
            ->paginate($perPage);

        return response()->json([
            'data' => $paginator->getCollection()->map(fn (HabitEntryHistory $h) => [
                'id' => $h->id,
                'habit_item' => $h->userHabitItem ? [
                    'id' => $h->userHabitItem->id,
                    'name' => $h->userHabitItem->name,
                    'icon' => $h->userHabitItem->icon,
                ] : null,
                'entry_date' => $h->entry_date?->toDateString(),
                'from_status' => $h->from_status?->value,
                'to_status' => $h->to_status?->value,
                'from_label' => $h->from_status?->label() ?? 'Trống',
                'to_label' => $h->to_status?->label() ?? 'Trống',
                'source' => $h->source,
                'changed_at' => $h->changed_at?->toIso8601String(),
            ]),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function grid(Request $request, User $user): JsonResponse
    {
        $today = $this->habitService->today();
        $year = (int) $request->query('year', $today->year);
        $month = (int) $request->query('month', $today->month);

        if ($month < 1 || $month > 12) {
            $month = (int) $today->month;
        }
        $minYear = (int) $today->year - 15;
        if ($year < $minYear || $year > (int) $today->year) {
            $year = (int) $today->year;
            $month = (int) $today->month;
        }

        $cursor = now(config('app.timezone'))->setDate($year, $month, 1)->startOfMonth();
        if ($cursor->gt($today->copy()->startOfMonth())) {
            $year = (int) $today->year;
            $month = (int) $today->month;
        }

        return response()->json([
            'data' => $this->habitService->monthGrid($user, $year, $month),
        ]);
    }
}
