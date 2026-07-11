<?php

declare(strict_types=1);

namespace App\Enums;

enum AvatarEffectType: string
{
    case Soft = 'soft';
    case Gradient = 'gradient';
    case Spin = 'spin';
    case Glow = 'glow';
    case Holographic = 'holographic';

    public function label(): string
    {
        return match ($this) {
            self::Soft => 'Viền mềm',
            self::Gradient => 'Gradient tĩnh',
            self::Spin => 'Viền xoay',
            self::Glow => 'Hào quang',
            self::Holographic => 'Pha lê / holographic',
        };
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
