<?php

declare(strict_types=1);

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Bộ lọc thời gian cho admin list: preset + khoảng tùy chọn.
 *
 * Query params:
 * - date_preset: today|yesterday|this_week|this_month|last_month|all
 * - date_from / date_to: ISO date (ưu tiên hơn preset khi có)
 */
final class AdminDateRange
{
    public const PRESET_TODAY = 'today';

    public const PRESET_YESTERDAY = 'yesterday';

    public const PRESET_THIS_WEEK = 'this_week';

    public const PRESET_THIS_MONTH = 'this_month';

    public const PRESET_LAST_MONTH = 'last_month';

    public const PRESET_ALL = 'all';

    /** @var list<string> */
    public const PRESETS = [
        self::PRESET_TODAY,
        self::PRESET_YESTERDAY,
        self::PRESET_THIS_WEEK,
        self::PRESET_THIS_MONTH,
        self::PRESET_LAST_MONTH,
        self::PRESET_ALL,
    ];

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    public static function apply(Builder $query, Request $request, string $column = 'created_at'): Builder
    {
        if ($request->filled('date_from') || $request->filled('date_to')) {
            if ($request->filled('date_from')) {
                $query->where(
                    $column,
                    '>=',
                    $request->date('date_from')?->startOfDay() ?? now()->startOfDay(),
                );
            }
            if ($request->filled('date_to')) {
                $query->where(
                    $column,
                    '<=',
                    $request->date('date_to')?->endOfDay() ?? now()->endOfDay(),
                );
            }

            return $query;
        }

        $preset = $request->input('date_preset');
        if (! is_string($preset) || $preset === '' || $preset === self::PRESET_ALL) {
            return $query;
        }

        [$start, $end] = self::boundsForPreset($preset);
        if ($start !== null && $end !== null) {
            $query->whereBetween($column, [$start, $end]);
        }

        return $query;
    }

    /**
     * @return array{0: ?CarbonInterface, 1: ?CarbonInterface}
     */
    public static function boundsForPreset(string $preset): array
    {
        $now = now();

        return match ($preset) {
            self::PRESET_TODAY => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            self::PRESET_YESTERDAY => [
                $now->copy()->subDay()->startOfDay(),
                $now->copy()->subDay()->endOfDay(),
            ],
            self::PRESET_THIS_WEEK => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            self::PRESET_THIS_MONTH => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            self::PRESET_LAST_MONTH => [
                $now->copy()->subMonthNoOverflow()->startOfMonth(),
                $now->copy()->subMonthNoOverflow()->endOfMonth(),
            ],
            default => [null, null],
        };
    }
}
