<?php

declare(strict_types=1);

namespace App\Enums;

enum ThermalNature: string
{
    case Cold = 'cold';
    case Cool = 'cool';
    case Neutral = 'neutral';
    case Warm = 'warm';
    case Hot = 'hot';

    public function label(): string
    {
        return match ($this) {
            self::Cold => __('what_to_eat.thermal_cold'),
            self::Cool => __('what_to_eat.thermal_cool'),
            self::Neutral => __('what_to_eat.thermal_neutral'),
            self::Warm => __('what_to_eat.thermal_warm'),
            self::Hot => __('what_to_eat.thermal_hot'),
        };
    }
}
