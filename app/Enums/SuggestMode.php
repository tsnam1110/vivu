<?php

declare(strict_types=1);

namespace App\Enums;

enum SuggestMode: string
{
    /** N option độc lập (legacy). */
    case Pick = 'pick';

    /** Ghép mâm theo template + dish_role. */
    case Compose = 'compose';

    /** Tự chọn compose khi phù hợp context. */
    case Auto = 'auto';

    public function label(): string
    {
        return match ($this) {
            self::Pick => __('what_to_eat.mode_suggest_pick'),
            self::Compose => __('what_to_eat.mode_suggest_compose'),
            self::Auto => __('what_to_eat.mode_suggest_auto'),
        };
    }
}
