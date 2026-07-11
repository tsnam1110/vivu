<?php

declare(strict_types=1);

namespace App\Enums;

enum ExperienceStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Published = 'published';
    case Hidden = 'hidden';

    public function isPublic(): bool
    {
        return $this === self::Published;
    }
}
