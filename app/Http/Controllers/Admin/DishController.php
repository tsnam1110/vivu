<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\DishStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDishRequest;
use App\Http\Resources\DishResource;
use App\Models\Dish;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class DishController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Dish::query()->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('q')) {
            $q = $request->string('q')->toString();
            $query->where(function ($builder) use ($q) {
                $builder->where('name', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%")
                    ->orWhere('search_keywords', 'like', "%{$q}%");
            });
        }

        return DishResource::collection($query->paginate(50));
    }

    public function store(StoreDishRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['status'] = $data['status'] ?? DishStatus::Published->value;
        $data['source'] = 'system';
        if (empty($data['slug'])) {
            $data['slug'] = Dish::uniqueSlugFromName($data['name']);
        } else {
            $data['slug'] = Str::slug($data['slug']) ?: Dish::uniqueSlugFromName($data['name']);
        }

        $dish = Dish::query()->create($data);

        return (new DishResource($dish))->response()->setStatusCode(201);
    }

    public function update(StoreDishRequest $request, Dish $dish): DishResource
    {
        $data = $request->validated();
        if (array_key_exists('slug', $data) && ($data['slug'] === null || $data['slug'] === '')) {
            unset($data['slug']);
        } elseif (! empty($data['slug'])) {
            $data['slug'] = Str::slug($data['slug']) ?: $dish->slug;
        }

        $dish->update($data);

        return new DishResource($dish->fresh());
    }

    public function destroy(Dish $dish): Response
    {
        $dish->delete();

        return response()->noContent();
    }
}
