<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\CycleHabitCellRequest;
use App\Http\Requests\Web\StoreUserHabitItemRequest;
use App\Http\Requests\Web\UpdateUserHabitItemRequest;
use App\Models\UserHabitItem;
use App\Services\HabitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HabitController extends Controller
{
    public function __construct(private readonly HabitService $habitService) {}

    public function grid(Request $request): JsonResponse
    {
        $user = $request->user('web');
        abort_unless($user && $user->isActive(), 403);

        $today = $this->habitService->today();
        $year = (int) $request->query('year', $today->year);
        $month = (int) $request->query('month', $today->month);

        return response()->json([
            'data' => $this->habitService->monthGrid($user, $year, $month),
        ]);
    }

    public function items(Request $request): JsonResponse
    {
        $user = $request->user('web');
        abort_unless($user && $user->isActive(), 403);

        $mine = UserHabitItem::query()
            ->forUser($user)
            ->orderByDesc('is_active')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (UserHabitItem $i) => [
                'id' => $i->id,
                'name' => $i->name,
                'description' => $i->description,
                'icon' => $i->icon,
                'is_active' => $i->is_active,
                'is_custom' => $i->isCustom(),
                'template_habit_item_id' => $i->template_habit_item_id,
                'sort_order' => $i->sort_order,
            ]);

        $templates = $this->habitService->availableTemplatesFor($user)->map(fn ($t) => [
            'id' => $t->id,
            'name' => $t->name,
            'description' => $t->description,
            'icon' => $t->icon,
        ]);

        return response()->json([
            'data' => [
                'items' => $mine,
                'templates' => $templates,
            ],
        ]);
    }

    public function storeItem(StoreUserHabitItemRequest $request): JsonResponse
    {
        $user = $request->user('web');
        $data = $request->validated();

        $item = $data['mode'] === 'template'
            ? $this->habitService->adoptTemplate($user, (int) $data['template_habit_item_id'])
            : $this->habitService->createCustom($user, $data);

        return response()->json([
            'data' => [
                'id' => $item->id,
                'name' => $item->name,
                'icon' => $item->icon,
                'is_custom' => $item->isCustom(),
            ],
        ], 201);
    }

    public function updateItem(UpdateUserHabitItemRequest $request, UserHabitItem $userHabitItem): JsonResponse
    {
        $item = $this->habitService->updateUserItem(
            $request->user('web'),
            $userHabitItem,
            $request->validated(),
        );

        return response()->json([
            'data' => [
                'id' => $item->id,
                'name' => $item->name,
                'icon' => $item->icon,
                'is_active' => $item->is_active,
                'is_custom' => $item->isCustom(),
            ],
        ]);
    }

    public function destroyItem(Request $request, UserHabitItem $userHabitItem): Response
    {
        $user = $request->user('web');
        abort_unless($user && $user->isActive(), 403);
        abort_unless($userHabitItem->user_id === $user->id, 403);

        $this->habitService->deleteUserItem($user, $userHabitItem);

        return response()->noContent();
    }

    public function cycle(CycleHabitCellRequest $request): JsonResponse
    {
        $result = $this->habitService->cycleCell(
            $request->user('web'),
            (int) $request->validated('user_habit_item_id'),
            $request->validated('date'),
            'api',
        );

        return response()->json(['data' => $result]);
    }

    public function history(Request $request): JsonResponse
    {
        $user = $request->user('web');
        abort_unless($user && $user->isActive(), 403);

        $paginator = $this->habitService->historyForUser($user, min(50, max(1, (int) $request->query('per_page', 30))));

        return response()->json([
            'data' => $paginator->getCollection()->map(fn ($h) => [
                'id' => $h->id,
                'habit_item' => $h->userHabitItem ? [
                    'id' => $h->userHabitItem->id,
                    'name' => $h->userHabitItem->name,
                    'icon' => $h->userHabitItem->icon,
                ] : null,
                'entry_date' => $h->entry_date?->toDateString(),
                'from_status' => $h->from_status?->value,
                'to_status' => $h->to_status?->value,
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
}
