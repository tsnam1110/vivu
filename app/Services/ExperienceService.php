<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ExperienceStatus;
use App\Jobs\ProcessExperienceImages;
use App\Models\Experience;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ExperienceService
{
    /**
     * @param  array<string, mixed>  $data
     * @param  list<int>|null  $tagIds
     * @param  list<UploadedFile>|null  $images
     */
    public function create(User $user, array $data, ?array $tagIds = null, ?array $images = null): Experience
    {
        return DB::transaction(function () use ($user, $data, $tagIds, $images) {
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
                'status' => $status,
                'published_at' => $status === ExperienceStatus::Published ? now() : null,
            ]);

            if ($tagIds) {
                $this->syncTags($experience, $tagIds);
            }

            if ($images) {
                $this->storeImages($experience, $images, isset($data['cover_index']) ? (int) $data['cover_index'] : 0);
            }

            return $experience->load(['category', 'tags', 'media', 'user']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<int>|null  $tagIds
     */
    public function update(Experience $experience, array $data, ?array $tagIds = null): Experience
    {
        return DB::transaction(function () use ($experience, $data, $tagIds) {
            $status = isset($data['status'])
                ? ExperienceStatus::from($data['status'])
                : $experience->status;

            $merged = array_merge($experience->only([
                'latitude', 'longitude', 'title', 'content', 'place_name',
                'address', 'google_place_id', 'category_id',
            ]), $data);

            $this->assertPublishable($status, $merged);

            $payload = collect($data)->only([
                'category_id', 'title', 'content', 'place_name', 'address',
                'latitude', 'longitude', 'google_place_id', 'status',
            ])->all();

            if ($status === ExperienceStatus::Published && ! $experience->published_at) {
                $payload['published_at'] = now();
            }

            $experience->update($payload);

            if ($tagIds !== null) {
                $this->syncTags($experience, $tagIds);
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
