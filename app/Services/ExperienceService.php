<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ExperienceStatus;
use App\Enums\TagStatus;
use App\Jobs\ProcessExperienceImages;
use App\Models\Experience;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ExperienceService
{
    /**
     * @param  array<string, mixed>  $data
     * @param  list<int|string>|null  $tagIds
     * @param  list<UploadedFile>|null  $images
     * @param  list<string>|null  $newTagNames
     */
    public function create(
        User $user,
        array $data,
        ?array $tagIds = null,
        ?array $images = null,
        ?array $newTagNames = null,
    ): Experience {
        return DB::transaction(function () use ($user, $data, $tagIds, $images, $newTagNames) {
            $status = ExperienceStatus::from($data['status'] ?? ExperienceStatus::Draft->value);
            $this->assertPublishable($status, $data);

            $experience = Experience::query()->create([
                'user_id' => $user->id,
                'category_id' => $data['category_id'],
                'title' => $data['title'],
                'content' => $data['content'] ?? null,
                'place_name' => $data['place_name'] ?? null,
                'address' => $data['address'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'google_place_id' => $data['google_place_id'] ?? null,
                'author_rating' => $data['author_rating'] ?? null,
                'status' => $status,
                'published_at' => $status === ExperienceStatus::Published ? now() : null,
            ]);

            $resolvedTagIds = $this->resolveTagIds(
                $user,
                (int) $data['category_id'],
                $tagIds,
                $newTagNames,
            );
            if ($resolvedTagIds !== []) {
                $this->syncTags($experience, $resolvedTagIds);
            }

            if ($images) {
                $this->storeImages($experience, $images, isset($data['cover_index']) ? (int) $data['cover_index'] : 0);
            }

            return $experience->load(['category', 'tags', 'media', 'user']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<int|string>|null  $tagIds
     * @param  list<string>|null  $newTagNames
     */
    public function update(
        Experience $experience,
        array $data,
        ?array $tagIds = null,
        ?array $newTagNames = null,
    ): Experience {
        return DB::transaction(function () use ($experience, $data, $tagIds, $newTagNames) {
            $status = isset($data['status'])
                ? ExperienceStatus::from($data['status'])
                : $experience->status;

            $merged = array_merge($experience->only([
                'latitude', 'longitude', 'title', 'content', 'place_name',
                'address', 'google_place_id', 'category_id', 'author_rating',
            ]), $data);

            $this->assertPublishable($status, $merged);

            $payload = collect($data)->only([
                'category_id', 'title', 'content', 'place_name', 'address',
                'latitude', 'longitude', 'google_place_id', 'author_rating', 'status',
            ])->all();

            if ($status === ExperienceStatus::Published && ! $experience->published_at) {
                $payload['published_at'] = now();
            }

            $experience->update($payload);

            if ($tagIds !== null || $newTagNames !== null) {
                $categoryId = (int) ($payload['category_id'] ?? $experience->category_id);
                $owner = $experience->user;
                $resolved = $this->resolveTagIds(
                    $owner,
                    $categoryId,
                    $tagIds ?? $experience->tags()->pluck('tags.id')->all(),
                    $newTagNames,
                );
                $this->syncTags($experience, $resolved);
            }

            return $experience->fresh(['category', 'tags', 'media', 'user']);
        });
    }

    public function delete(Experience $experience): void
    {
        $experience->delete();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function listPublished(array $filters = []): LengthAwarePaginator
    {
        $perPage = min((int) ($filters['per_page'] ?? 15), 50);
        $query = Experience::query()
            ->published()
            ->with(['category', 'tags', 'user', 'media']);

        if (! empty($filters['category'])) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $filters['category']));
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['tags']) && is_array($filters['tags'])) {
            foreach ($filters['tags'] as $tag) {
                $query->whereHas('tags', function ($q) use ($tag) {
                    $q->where('tags.status', TagStatus::Approved);
                    is_numeric($tag)
                        ? $q->where('tags.id', $tag)
                        : $q->where('tags.slug', $tag);
                });
            }
        }

        if (! empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(function ($builder) use ($q) {
                $builder->where('title', 'like', "%{$q}%")
                    ->orWhere('content', 'like', "%{$q}%")
                    ->orWhere('place_name', 'like', "%{$q}%")
                    ->orWhere('address', 'like', "%{$q}%");
            });
        }

        if (isset($filters['lat'], $filters['lng'])) {
            $lat = (float) $filters['lat'];
            $lng = (float) $filters['lng'];
            $radius = (float) ($filters['radius_km'] ?? 5);
            $query->nearby($lat, $lng, $radius);
        }

        $sort = $filters['sort'] ?? '-published_at';
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $column = ltrim($sort, '-');
        $allowed = ['published_at', 'rating_avg', 'reaction_count', 'created_at'];
        if (! in_array($column, $allowed, true)) {
            $column = 'published_at';
        }
        $query->orderBy($column, $direction);

        $paginator = $query->paginate($perPage);

        if (isset($filters['lat'], $filters['lng'])) {
            $lat = (float) $filters['lat'];
            $lng = (float) $filters['lng'];
            $radius = (float) ($filters['radius_km'] ?? 5);
            $paginator->setCollection(
                $paginator->getCollection()->filter(function (Experience $exp) use ($lat, $lng, $radius) {
                    return $this->haversineKm($lat, $lng, (float) $exp->latitude, (float) $exp->longitude) <= $radius;
                })->values()
            );
        }

        return $paginator;
    }

    public function incrementView(Experience $experience): void
    {
        $experience->increment('view_count');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function assertPublishable(ExperienceStatus $status, array $data): void
    {
        if ($status !== ExperienceStatus::Published) {
            return;
        }

        if (! isset($data['latitude'], $data['longitude']) || $data['latitude'] === null || $data['longitude'] === null) {
            throw ValidationException::withMessages([
                'latitude' => [__('validation.published_requires_coordinates')],
            ]);
        }
    }

    /**
     * Gộp id thẻ đã chọn + tạo thẻ pending từ tên mới.
     *
     * @param  list<int|string>|null  $tagIds
     * @param  list<string>|null  $newTagNames
     * @return list<int>
     */
    public function resolveTagIds(User $user, int $categoryId, ?array $tagIds, ?array $newTagNames): array
    {
        $ids = collect($tagIds ?? [])
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        // Chỉ gắn thẻ approved hoặc pending do chính user tạo
        if ($ids->isNotEmpty()) {
            $allowed = Tag::query()
                ->whereIn('id', $ids->all())
                ->visibleTo($user)
                ->pluck('id');
            $ids = $ids->intersect($allowed)->values();
        }

        foreach (collect($newTagNames ?? [])->filter() as $name) {
            $name = trim((string) $name);
            if ($name === '' || mb_strlen($name) > 80) {
                continue;
            }

            $tag = $this->findOrCreateUserTag($user, $categoryId, $name);
            $ids->push($tag->id);
        }

        return $ids->unique()->take(10)->values()->all();
    }

    private function findOrCreateUserTag(User $user, int $categoryId, string $name): Tag
    {
        $slug = Str::slug($name) ?: 'tag-'.Str::lower(Str::random(6));

        // Ưu tiên thẻ đã duyệt cùng tên/slug (toàn cục hoặc cùng danh mục)
        $existing = Tag::query()
            ->where(function ($q) use ($categoryId) {
                $q->whereNull('category_id')->orWhere('category_id', $categoryId);
            })
            ->where(function ($q) use ($name, $slug) {
                $q->where('slug', $slug)->orWhereRaw('LOWER(name) = ?', [mb_strtolower($name)]);
            })
            ->where(function ($q) use ($user) {
                $q->where('status', TagStatus::Approved)
                    ->orWhere(function ($inner) use ($user) {
                        $inner->where('status', TagStatus::Pending)
                            ->where('created_by', $user->id);
                    });
            })
            ->orderByRaw("CASE WHEN status = 'approved' THEN 0 ELSE 1 END")
            ->first();

        if ($existing) {
            return $existing;
        }

        return Tag::query()->create([
            'category_id' => $categoryId,
            'name' => $name,
            'slug' => $this->uniqueTagSlug($categoryId, $slug),
            'status' => TagStatus::Pending,
            'created_by' => $user->id,
            'usage_count' => 0,
        ]);
    }

    private function uniqueTagSlug(int $categoryId, string $base): string
    {
        $slug = $base;
        $i = 1;
        while (
            Tag::query()
                ->where('category_id', $categoryId)
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }

    /**
     * @param  list<int>  $tagIds
     */
    private function syncTags(Experience $experience, array $tagIds): void
    {
        $previous = $experience->tags()->pluck('tags.id')->all();
        $experience->tags()->sync($tagIds);

        $removed = array_diff($previous, $tagIds);
        $added = array_diff($tagIds, $previous);

        if ($removed) {
            Tag::query()->whereIn('id', $removed)->where('usage_count', '>', 0)->decrement('usage_count');
        }
        if ($added) {
            Tag::query()->whereIn('id', $added)->increment('usage_count');
        }
    }

    /**
     * @param  list<UploadedFile>  $images
     */
    private function storeImages(Experience $experience, array $images, int $coverIndex = 0): void
    {
        foreach ($images as $index => $file) {
            $path = $file->store('experiences/'.$experience->id, 'public');
            $experience->media()->create([
                'disk' => 'public',
                'path' => $path,
                'is_cover' => $index === $coverIndex,
                'sort_order' => $index,
            ]);
        }

        ProcessExperienceImages::dispatch($experience->id);
    }

    public function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earth = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 2 * $earth * asin(min(1, sqrt($a)));
    }
}
