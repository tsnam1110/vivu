<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTasteTraitRequest;
use App\Http\Resources\TasteTraitResource;
use App\Models\TasteTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TasteTraitController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = TasteTrait::query()->orderBy('type')->orderBy('name');

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

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

        return TasteTraitResource::collection($query->get());
    }

    public function store(StoreTasteTraitRequest $request): JsonResponse
    {
        $trait = TasteTrait::query()->create($request->validated());

        return (new TasteTraitResource($trait))->response()->setStatusCode(201);
    }

    public function update(StoreTasteTraitRequest $request, TasteTrait $tasteTrait): TasteTraitResource
    {
        $tasteTrait->update($request->validated());

        return new TasteTraitResource($tasteTrait->fresh());
    }

    public function destroy(TasteTrait $tasteTrait): Response
    {
        $tasteTrait->delete();

        return response()->noContent();
    }
}
