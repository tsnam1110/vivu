<?php

declare(strict_types=1);

namespace App\Enums;

enum MealSlot: string
{
    case Breakfast = 'breakfast';
    case Lunch = 'lunch';
    case Dinner = 'dinner';

    public function label(): string
    {
        return match ($this) {
            self::Breakfast => __('what_to_eat.slot_breakfast'),
            self::Lunch => __('what_to_eat.slot_lunch'),
            self::Dinner => __('what_to_eat.slot_dinner'),
        };
    }
}
