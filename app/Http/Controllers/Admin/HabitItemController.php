<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreHabitItemRequest;
use App\Http\Requests\Admin\UpdateHabitItemRequest;
use App\Http\Resources\HabitItemResource;
use App\Models\HabitItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class HabitItemController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = HabitItem::query()->orderBy('sort_order')->orderBy('name');

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('q')) {
            $q = $request->string('q')->toString();
            $query->where(function ($builder) use ($q) {
                $builder->where('name', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%");
            });
        }

        return HabitItemResource::collection($query->get());
    }

    public function store(StoreHabitItemRequest $request): JsonResponse
    {
        $item = HabitItem::query()->create($request->validated());

        return (new HabitItemResource($item))->response()->setStatusCode(201);
    }

    public function update(UpdateHabitItemRequest $request, HabitItem $habitItem): HabitItemResource
    {
        $habitItem->update($request->validated());

        return new HabitItemResource($habitItem->fresh());
    }

    public function destroy(HabitItem $habitItem): Response
    {
        $habitItem->delete();

        return response()->noContent();
    }
}
