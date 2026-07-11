<?php

declare(strict_types=1);

namespace App\Enums;

enum MealMode: string
{
    case DineOut = 'dine_out';
    case CookHome = 'cook_home';

    public function label(): string
    {
        return match ($this) {
            self::DineOut => __('what_to_eat.mode_dine_out'),
            self::CookHome => __('what_to_eat.mode_cook_home'),
        };
    }
}
