<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DishRole;
use App\Enums\MealMode;
use App\Enums\MealSize;
use App\Enums\MealSlot;

/**
 * Template cấu trúc mâm — nguồn logic lớp B (ruleset).
 *
 * @see docs/features/what-to-eat-ruleset.md
 */
class MealTemplateRegistry
{
    /**
     * @return array{
     *     id: string,
     *     label: string,
     *     summary: string,
     *     slots: list<array{key: string, role: string, label: string, required: bool, calorie_share: float}>,
     *     implicit: list<array{type: string, label: string, kcal_estimate: int|null}>,
     *     rules: list<string>
     * }|null
     */
    public function resolve(MealSlot $slot, MealSize $size, MealMode $mode, int $count): ?array
    {
        if ($size === MealSize::Light) {
            return $this->templateLight1();
        }

        if ($mode === MealMode::DineOut) {
            return $count <= 1
                ? $this->templateDineOut1()
                : $this->templateDineOut1(); // v0.2: multi dine_out vẫn one-bowl pick qua fallback
        }

        // cook_home · main
        return match (true) {
            $count <= 1 => $this->templateStandalone1(),
            $count === 2 => $this->templateVnHome2(),
            default => $this->templateVnHome3(), // 3–5: core 3 roles (extra count ignored as extra options later)
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function templateVnHome3(): array
    {
        return [
            'id' => 'vn_home_3',
            'label' => __('what_to_eat.template_vn_home_3'),
            'summary' => __('what_to_eat.template_vn_home_3_summary'),
            'slots' => [
                $this->slot('soup', DishRole::Soup, __('what_to_eat.role_soup'), true, 0.20),
                $this->slot('main', DishRole::MainProtein, __('what_to_eat.role_main_protein'), true, 0.50),
                $this->slot('veg', DishRole::SideVeg, __('what_to_eat.role_side_veg'), true, 0.30),
            ],
            'implicit' => [$this->implicitRice()],
            'rules' => ['B01_template_roles', 'B02_no_feast_in_home_plate', 'B03_no_double_one_bowl', 'B05_slot_meal_flags'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function templateVnHome2(): array
    {
        return [
            'id' => 'vn_home_2_veg',
            'label' => __('what_to_eat.template_vn_home_2'),
            'summary' => __('what_to_eat.template_vn_home_2_summary'),
            'slots' => [
                $this->slot('main', DishRole::MainProtein, __('what_to_eat.role_main_protein'), true, 0.65),
                $this->slot('veg', DishRole::SideVeg, __('what_to_eat.role_side_veg'), true, 0.35),
            ],
            'implicit' => [$this->implicitRice()],
            'rules' => ['B01_template_roles', 'B02_no_feast_in_home_plate', 'B03_no_double_one_bowl'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function templateStandalone1(): array
    {
        return [
            'id' => 'standalone_1',
            'label' => __('what_to_eat.template_standalone_1'),
            'summary' => __('what_to_eat.template_standalone_1_summary'),
            'slots' => [
                $this->slot('main', DishRole::OneBowl, __('what_to_eat.role_one_bowl'), true, 1.0),
            ],
            'implicit' => [],
            'rules' => ['B01_template_roles', 'B05_slot_meal_flags'],
            'fallback_roles' => [DishRole::MainProtein->value],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function templateLight1(): array
    {
        return [
            'id' => 'light_1',
            'label' => __('what_to_eat.template_light_1'),
            'summary' => __('what_to_eat.template_light_1_summary'),
            'slots' => [
                $this->slot('light', DishRole::DessertLight, __('what_to_eat.role_dessert_light'), true, 1.0),
            ],
            'implicit' => [],
            'rules' => ['B01_template_roles'],
            'fallback_roles' => [DishRole::SideVeg->value, DishRole::Beverage->value],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function templateDineOut1(): array
    {
        return [
            'id' => 'dine_out_1',
            'label' => __('what_to_eat.template_dine_out_1'),
            'summary' => __('what_to_eat.template_dine_out_1_summary'),
            'slots' => [
                $this->slot('main', DishRole::OneBowl, __('what_to_eat.role_one_bowl'), true, 1.0),
            ],
            'implicit' => [],
            'rules' => ['B01_template_roles'],
            'fallback_roles' => [DishRole::ShareFeast->value, DishRole::MainProtein->value],
        ];
    }

    /**
     * @return array{key: string, role: string, label: string, required: bool, calorie_share: float}
     */
    private function slot(string $key, DishRole $role, string $label, bool $required, float $share): array
    {
        return [
            'key' => $key,
            'role' => $role->value,
            'label' => $label,
            'required' => $required,
            'calorie_share' => $share,
        ];
    }

    /**
     * @return array{type: string, label: string, kcal_estimate: int|null}
     */
    private function implicitRice(): array
    {
        $kcal = config('what_to_eat.implicit_rice_kcal');

        return [
            'type' => 'rice',
            'label' => __('what_to_eat.implicit_rice'),
            'kcal_estimate' => is_numeric($kcal) ? (int) $kcal : null,
        ];
    }
}
