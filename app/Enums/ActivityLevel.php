<?php

declare(strict_types=1);

namespace App\Enums;

enum ActivityLevel: string
{
    case Sedentary = 'sedentary';
    case Light = 'light';
    case Moderate = 'moderate';
    case Active = 'active';
    case VeryActive = 'very_active';

    public function label(): string
    {
        return match ($this) {
            self::Sedentary => __('profile.activity_sedentary'),
            self::Light => __('profile.activity_light'),
            self::Moderate => __('profile.activity_moderate'),
            self::Active => __('profile.activity_active'),
            self::VeryActive => __('profile.activity_very_active'),
        };
    }

    /** Hệ số TDEE (nhân với BMR). */
    public function multiplier(): float
    {
        return match ($this) {
            self::Sedentary => 1.2,
            self::Light => 1.375,
            self::Moderate => 1.55,
            self::Active => 1.725,
            self::VeryActive => 1.9,
        };
    }
}
