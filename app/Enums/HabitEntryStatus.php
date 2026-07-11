<?php

declare(strict_types=1);

namespace App\Enums;

enum HabitEntryStatus: string
{
    case Done = 'done';
    case Missed = 'missed';

    public function label(): string
    {
        return match ($this) {
            self::Done => 'Đạt',
            self::Missed => 'Không đạt',
        };
    }

    public function symbol(): string
    {
        return match ($this) {
            self::Done => '✓',
            self::Missed => '✗',
        };
    }

    /**
     * Cycle: null → done → missed → null
     */
    public static function next(?self $current): ?self
    {
        return match ($current) {
            null => self::Done,
            self::Done => self::Missed,
            self::Missed => null,
        };
    }
}
