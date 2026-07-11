<?php

declare(strict_types=1);

namespace App\Enums;

enum ContributionStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('what_to_eat.status_pending'),
            self::Approved => __('what_to_eat.status_approved'),
            self::Rejected => __('what_to_eat.status_rejected'),
        };
    }
}
