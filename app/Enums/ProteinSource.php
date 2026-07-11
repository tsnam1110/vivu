<?php

declare(strict_types=1);

namespace App\Enums;

enum ProteinSource: string
{
    case Meat = 'meat';
    case Seafood = 'seafood';
    case Egg = 'egg';
    case Plant = 'plant';
    case Mixed = 'mixed';
    case None = 'none';
}
