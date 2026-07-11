<?php

declare(strict_types=1);

namespace App\Enums;

enum PremiumSource: string
{
    case Admin = 'admin';
    case Demo = 'demo';
    case Payment = 'payment';
}
