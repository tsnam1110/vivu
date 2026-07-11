<?php

declare(strict_types=1);

namespace App\Enums;

enum MealSize: string
{
    case Light = 'light';
    case Main = 'main';

    public function label(): string
    {
        return match ($this) {
            self::Light => __('what_to_eat.size_light'),
            self::Main => __('what_to_eat.size_main'),
        };
    }
}
