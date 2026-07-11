<?php

declare(strict_types=1);

namespace App\Enums;

enum DishRole: string
{
    case Soup = 'soup';
    case MainProtein = 'main_protein';
    case SideVeg = 'side_veg';
    case SideExtra = 'side_extra';
    case Starch = 'starch';
    case OneBowl = 'one_bowl';
    case DessertLight = 'dessert_light';
    case Beverage = 'beverage';
    case ShareFeast = 'share_feast';

    public function label(): string
    {
        return match ($this) {
            self::Soup => __('what_to_eat.role_soup'),
            self::MainProtein => __('what_to_eat.role_main_protein'),
            self::SideVeg => __('what_to_eat.role_side_veg'),
            self::SideExtra => __('what_to_eat.role_side_extra'),
            self::Starch => __('what_to_eat.role_starch'),
            self::OneBowl => __('what_to_eat.role_one_bowl'),
            self::DessertLight => __('what_to_eat.role_dessert_light'),
            self::Beverage => __('what_to_eat.role_beverage'),
            self::ShareFeast => __('what_to_eat.role_share_feast'),
        };
    }
}
