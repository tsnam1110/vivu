<?php

declare(strict_types=1);

namespace App\Enums;

enum FiveElement: string
{
    case Wood = 'wood';
    case Fire = 'fire';
    case Earth = 'earth';
    case Metal = 'metal';
    case Water = 'water';

    public function label(): string
    {
        return match ($this) {
            self::Wood => __('what_to_eat.element_wood'),
            self::Fire => __('what_to_eat.element_fire'),
            self::Earth => __('what_to_eat.element_earth'),
            self::Metal => __('what_to_eat.element_metal'),
            self::Water => __('what_to_eat.element_water'),
        };
    }

    public function emoji(): string
    {
        return match ($this) {
            self::Wood => '🌳',
            self::Fire => '🔥',
            self::Earth => '🌍',
            self::Metal => '⚙️',
            self::Water => '💧',
        };
    }
}
