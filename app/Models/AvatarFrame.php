<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AvatarEffectType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class AvatarFrame extends Model
{
    public const CACHE_KEY = 'avatar_frames:active';

    protected $fillable = [
        'slug',
        'name',
        'description',
        'effect_type',
        'effect_config',
        'is_premium',
        'show_badge',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'effect_type' => AvatarEffectType::class,
            'effect_config' => 'array',
            'is_premium' => 'boolean',
            'show_badge' => 'boolean',
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

    public function scopeFree(Builder $query): Builder
    {
        return $query->where('is_premium', false);
    }

    public function scopePremium(Builder $query): Builder
    {
        return $query->where('is_premium', true);
    }

    /**
     * CSS custom properties derived from effect_config (safe subset).
     *
     * @return array<string, string>
     */
    public function cssVariables(): array
    {
        $cfg = $this->effect_config ?? [];
        $colors = $cfg['colors'] ?? ['#a8a29e', '#d6d3d1'];
        if (! is_array($colors) || $colors === []) {
            $colors = ['#a8a29e', '#d6d3d1'];
        }

        $thickness = max(1, min(8, (int) ($cfg['thickness'] ?? 3)));
        $speed = max(800, min(12000, (int) ($cfg['speed_ms'] ?? 3000)));
        $intensity = max(0.1, min(1.0, (float) ($cfg['intensity'] ?? 0.6)));

        $safeColors = array_values(array_filter(
            array_map(fn ($c) => is_string($c) && preg_match('/^#[0-9A-Fa-f]{3,8}$/', $c) ? $c : null, $colors)
        ));
        if ($safeColors === []) {
            $safeColors = ['#a8a29e', '#d6d3d1'];
        }

        while (count($safeColors) < 3) {
            $safeColors[] = $safeColors[count($safeColors) - 1];
        }

        return [
            '--af-c1' => $safeColors[0],
            '--af-c2' => $safeColors[1],
            '--af-c3' => $safeColors[2],
            '--af-gradient' => 'linear-gradient(135deg, '.implode(', ', array_slice($safeColors, 0, 4)).')',
            '--af-thickness' => $thickness.'px',
            '--af-speed' => $speed.'ms',
            '--af-intensity' => (string) $intensity,
        ];
    }

    public function cssVariablesString(): string
    {
        $parts = [];
        foreach ($this->cssVariables() as $key => $value) {
            $parts[] = $key.': '.$value;
        }

        return implode('; ', $parts);
    }

    public function effectClass(): string
    {
        $type = $this->effect_type instanceof AvatarEffectType
            ? $this->effect_type->value
            : (string) $this->effect_type;

        return 'af-frame af-'.$type;
    }

    /**
     * Cache plain attribute arrays (not Eloquent models) to avoid
     * __PHP_Incomplete_Class when unserializing enums/models from the cache store.
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
                    ->map(fn (self $frame) => $frame->getAttributes())
                    ->all();
            });
        } catch (\Throwable) {
            Cache::forget(self::CACHE_KEY);
            $rows = self::query()
                ->active()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->map(fn (self $frame) => $frame->getAttributes())
                ->all();
            Cache::put(self::CACHE_KEY, $rows, now()->addHour());
        }

        // Stale/corrupt payload (e.g. serialized Collection of models from older code)
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
