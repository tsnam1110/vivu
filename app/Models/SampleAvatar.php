<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class SampleAvatar extends Model
{
    public const CACHE_KEY = 'sample_avatars:active';

    protected $fillable = [
        'slug',
        'name',
        'path',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function url(): string
    {
        return asset($this->path);
    }

    /**
     * Cache plain attribute arrays (not Eloquent models) to avoid
     * __PHP_Incomplete_Class when unserializing from the cache store.
     *
     * @return \Illuminate\Support\Collection<int, self>
     */
    public static function cachedActive(): \Illuminate\Support\Collection
    {
        try {
            $rows = Cache::remember(self::CACHE_KEY, now()->addHour(), function () {
                return self::query()
                    ->active()
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->get()
                    ->map(fn (self $sample) => $sample->getAttributes())
                    ->all();
            });
        } catch (\Throwable) {
            Cache::forget(self::CACHE_KEY);
            $rows = self::query()
                ->active()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->map(fn (self $sample) => $sample->getAttributes())
                ->all();
            Cache::put(self::CACHE_KEY, $rows, now()->addHour());
        }

        if (! is_array($rows)) {
            Cache::forget(self::CACHE_KEY);

            return self::query()->active()->orderBy('sort_order')->orderBy('id')->get();
        }

        return collect($rows)->map(function (array $attributes) {
            $model = static::newModelInstance();
            $model->setRawAttributes($attributes, true);
            $model->exists = true;

            return $model;
        });
    }

    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    protected static function booted(): void
    {
        static::saved(fn () => self::flushCache());
        static::deleted(fn () => self::flushCache());
    }
}
