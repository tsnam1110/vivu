<?php

declare(strict_types=1);

namespace App\Enums;

enum ContributionType: string
{
    case Recipe = 'recipe';
    case Calories = 'calories';
    case Harm = 'harm';
    case Benefit = 'benefit';
    case Advice = 'advice';
    case Note = 'note';
    case FiveElement = 'five_element';

    public function label(): string
    {
        return match ($this) {
            self::Recipe => __('what_to_eat.contrib_recipe'),
            self::Calories => __('what_to_eat.contrib_calories'),
            self::Harm => __('what_to_eat.contrib_harm'),
            self::Benefit => __('what_to_eat.contrib_benefit'),
            self::Advice => __('what_to_eat.contrib_advice'),
            self::Note => __('what_to_eat.contrib_note'),
            self::FiveElement => __('what_to_eat.contrib_element'),
        };
    }
}
