<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ContributionStatus;
use App\Enums\ContributionType;
use App\Enums\FiveElement;
use App\Models\Admin;
use App\Models\Dish;
use App\Models\DishContribution;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DishContributionService
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function submit(User $user, Dish $dish, ContributionType|string $type, array $payload): DishContribution
    {
        $type = $type instanceof ContributionType ? $type : ContributionType::from($type);
        $this->assertPayload($type, $payload);

        return DishContribution::query()->create([
            'dish_id' => $dish->id,
            'user_id' => $user->id,
            'type' => $type,
            'payload' => $payload,
            'status' => ContributionStatus::Pending,
            'is_canonical' => false,
        ]);
    }

    public function approve(DishContribution $contribution, Admin $admin, bool $setCanonical = true, ?string $note = null): DishContribution
    {
        return DB::transaction(function () use ($contribution, $admin, $setCanonical, $note) {
            $contribution->refresh();

            if ($contribution->status === ContributionStatus::Approved) {
                return $contribution;
            }

            if ($setCanonical) {
                DishContribution::query()
                    ->where('dish_id', $contribution->dish_id)
                    ->where('type', $contribution->type)
                    ->where('is_canonical', true)
                    ->update(['is_canonical' => false]);
            }

            $contribution->update([
                'status' => ContributionStatus::Approved,
                'is_canonical' => $setCanonical,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
                'review_note' => $note,
            ]);

            if ($setCanonical) {
                $this->syncCanonicalToDish($contribution->fresh(['dish']));
            }

            return $contribution->fresh(['dish', 'user', 'reviewer']);
        });
    }

    public function reject(DishContribution $contribution, Admin $admin, ?string $note = null): DishContribution
    {
        $contribution->update([
            'status' => ContributionStatus::Rejected,
            'is_canonical' => false,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
            'review_note' => $note,
        ]);

        return $contribution->fresh(['dish', 'user', 'reviewer']);
    }

    public function syncCanonicalToDish(DishContribution $contribution): void
    {
        $dish = $contribution->dish;
        if (! $dish) {
            return;
        }

        $payload = $contribution->payload ?? [];

        match ($contribution->type) {
            ContributionType::Recipe => $dish->update([
                'ingredients' => $payload['ingredients'] ?? $dish->ingredients,
                'steps' => $payload['steps'] ?? $dish->steps,
                'cook_minutes' => isset($payload['cook_minutes'])
                    ? (int) $payload['cook_minutes']
                    : $dish->cook_minutes,
            ]),
            ContributionType::Calories => $dish->update([
                'calories_kcal' => isset($payload['kcal_per_serving'])
                    ? (int) $payload['kcal_per_serving']
                    : $dish->calories_kcal,
                'serving_grams' => isset($payload['serving_grams'])
                    ? (int) $payload['serving_grams']
                    : ($dish->serving_grams ?? 100),
            ]),
            ContributionType::Harm => $dish->update([
                'harms' => (string) ($payload['body'] ?? $dish->harms),
            ]),
            ContributionType::Benefit => $dish->update([
                'benefits' => (string) ($payload['body'] ?? $dish->benefits),
            ]),
            ContributionType::Advice => $dish->update([
                'advice' => (string) ($payload['body'] ?? $dish->advice),
            ]),
            ContributionType::Note => $dish->update([
                'notes' => (string) ($payload['body'] ?? $dish->notes),
            ]),
            ContributionType::FiveElement => $dish->update([
                'five_element' => isset($payload['element'])
                    ? FiveElement::from((string) $payload['element'])
                    : $dish->five_element,
            ]),
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function assertPayload(ContributionType $type, array $payload): void
    {
        $fail = function (string $message): never {
            throw ValidationException::withMessages(['payload' => $message]);
        };

        match ($type) {
            ContributionType::Recipe => (function () use ($payload, $fail) {
                if (empty($payload['steps']) || ! is_array($payload['steps'])) {
                    $fail(__('what_to_eat.validation_recipe_steps'));
                }
            })(),
            ContributionType::Calories => (function () use ($payload, $fail) {
                if (! isset($payload['kcal_per_serving']) || ! is_numeric($payload['kcal_per_serving'])) {
                    $fail(__('what_to_eat.validation_calories'));
                }
                if (isset($payload['serving_grams']) && (! is_numeric($payload['serving_grams']) || (int) $payload['serving_grams'] < 1)) {
                    $fail(__('what_to_eat.validation_serving_grams'));
                }
            })(),
            ContributionType::Harm, ContributionType::Benefit, ContributionType::Advice, ContributionType::Note => (function () use ($payload, $fail) {
                $body = trim((string) ($payload['body'] ?? ''));
                if ($body === '' || mb_strlen($body) > 2000) {
                    $fail(__('what_to_eat.validation_body'));
                }
            })(),
            ContributionType::FiveElement => (function () use ($payload, $fail) {
                $el = (string) ($payload['element'] ?? '');
                if (FiveElement::tryFrom($el) === null) {
                    $fail(__('what_to_eat.validation_element'));
                }
            })(),
        };
    }
}
