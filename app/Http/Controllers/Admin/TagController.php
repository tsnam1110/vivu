<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\TagStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTagRequest;
use App\Http\Requests\Admin\UpdateTagStatusRequest;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class TagController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Tag::query()->with(['category', 'creator'])->orderByDesc('id');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('q')) {
            $q = $request->string('q')->toString();
            $query->where(function ($builder) use ($q) {
                $builder->where('name', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%");
            });
        }

        return TagResource::collection($query->paginate(50));
    }

    public function store(StoreTagRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['status'] = $data['status'] ?? TagStatus::Approved->value;
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $tag = Tag::query()->create($data);

        return (new TagResource($tag->load(['category', 'creator'])))->response()->setStatusCode(201);
    }

    public function update(StoreTagRequest $request, Tag $tag): TagResource
    {
        $data = $request->validated();
        if (array_key_exists('slug', $data) && ($data['slug'] === null || $data['slug'] === '')) {
            unset($data['slug']);
        }
        $tag->update($data);

        return new TagResource($tag->fresh(['category', 'creator']));
    }

    public function updateStatus(UpdateTagStatusRequest $request, Tag $tag): TagResource
    {
        $tag->update(['status' => $request->validated('status')]);

        return new TagResource($tag->fresh(['category', 'creator']));
    }

    public function destroy(Tag $tag): Response
    {
        $tag->delete();

        return response()->noContent();
    }
}
