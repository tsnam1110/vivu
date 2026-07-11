<?php

declare(strict_types=1);

namespace App\Enums;

enum CommentStatus: string
{
    case Visible = 'visible';
    case Pending = 'pending';
    case Hidden = 'hidden';
}
