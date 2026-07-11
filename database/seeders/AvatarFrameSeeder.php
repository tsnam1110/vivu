<?php

namespace Database\Seeders;

use App\Enums\AvatarEffectType;
use App\Models\AvatarFrame;
use Illuminate\Database\Seeder;

class AvatarFrameSeeder extends Seeder
{
    public function run(): void
    {
        $frames = [
            [
                'slug' => 'soft',
                'name' => 'Viền mềm',
                'description' => 'Viền xám nhẹ, miễn phí cho mọi thành viên.',
                'effect_type' => AvatarEffectType::Soft,
                'effect_config' => [
                    'colors' => ['#d6d3d1', '#a8a29e'],
                    'thickness' => 3,
                    'intensity' => 0.4,
                ],
                'is_premium' => false,
                'show_badge' => false,
                'sort_order' => 10,
            ],
            [
                'slug' => 'gold',
                'name' => 'Hoàng kim',
                'description' => 'Viền vàng xoay sang trọng — phong cách Discord Nitro.',
                'effect_type' => AvatarEffectType::Spin,
                'effect_config' => [
                    'colors' => ['#fde68a', '#fbbf24', '#d97706', '#f59e0b'],
                    'thickness' => 3,
                    'speed_ms' => 2800,
                    'intensity' => 0.85,
                ],
                'is_premium' => true,
                'show_badge' => true,
                'sort_order' => 20,
            ],
            [
                'slug' => 'aurora',
                'name' => 'Cực quang',
                'description' => 'Gradient hồng–tím–cyan xoay mượt.',
                'effect_type' => AvatarEffectType::Spin,
                'effect_config' => [
                    'colors' => ['#e879f9', '#8b5cf6', '#22d3ee', '#a78bfa'],
                    'thickness' => 3,
                    'speed_ms' => 3200,
                    'intensity' => 0.9,
                ],
                'is_premium' => true,
                'show_badge' => true,
                'sort_order' => 30,
            ],
            [
                'slug' => 'crystal',
                'name' => 'Pha lê',
                'description' => 'Hiệu ứng holographic lấp lánh như skin hiếm.',
                'effect_type' => AvatarEffectType::Holographic,
                'effect_config' => [
                    'colors' => ['#67e8f9', '#c4b5fd', '#f9a8d4', '#fde68a'],
                    'thickness' => 3,
                    'speed_ms' => 4000,
                    'intensity' => 0.8,
                ],
                'is_premium' => true,
                'show_badge' => true,
                'sort_order' => 40,
            ],
            [
                'slug' => 'ember',
                'name' => 'Than hồng',
                'description' => 'Hào quang cam–đỏ nhịp thở, phong cách game.',
                'effect_type' => AvatarEffectType::Glow,
                'effect_config' => [
                    'colors' => ['#fb923c', '#f43f5e', '#ea580c'],
                    'thickness' => 3,
                    'speed_ms' => 2200,
                    'intensity' => 0.75,
                ],
                'is_premium' => true,
                'show_badge' => true,
                'sort_order' => 50,
            ],
        ];

        foreach ($frames as $frame) {
            AvatarFrame::query()->updateOrCreate(
                ['slug' => $frame['slug']],
                $frame + ['is_active' => true],
            );
        }

        AvatarFrame::flushCache();
    }
}
