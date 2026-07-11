<?php

declare(strict_types=1);

namespace App\Enums;

enum Gender: string
{
    case Male = 'male';
    case Female = 'female';
    case Other = 'other';
    case PreferNot = 'prefer_not';

    public function label(): string
    {
        return match ($this) {
            self::Male => __('profile.gender_male'),
            self::Female => __('profile.gender_female'),
            self::Other => __('profile.gender_other'),
            self::PreferNot => __('profile.gender_prefer_not'),
        };
    }
}
