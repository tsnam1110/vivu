<?php

declare(strict_types=1);

namespace App\Enums;

enum CookingMethod: string
{
    case Boil = 'boil';
    case Steam = 'steam';
    case Grill = 'grill';
    case Fry = 'fry';
    case Raw = 'raw';
    case Braise = 'braise';
    case SoupBase = 'soup_base';
    case Mixed = 'mixed';
}
