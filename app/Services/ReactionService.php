<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ReactionType;
use App\Models\Experience;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ReactionService
{
    /**
     * Upsert reaction. Same type again = toggle off.
     *
     * @return array{type: string|null, counts: array<string, int>, total: int}
     */
    public function react(Model $reactable, User $user, ReactionType $type): array
    {
        return DB::transaction(function () use ($reactable, $user, $type) {
            $existing = Reaction::query()
                ->where('user_id', $user->id)
                ->where('reactable_type', $reactable->getMorphClass())
                ->where('reactable_id', $reactable->getKey())
                ->first();

            $resultType = $type->value;

            if ($existing) {
                if ($existing->type === $type) {
                    $existing->delete();
                    $resultType = null;
                } else {
                    $existing->update(['type' => $type]);
                }
            } else {
                Reaction::query()->create([
                    'user_id' => $user->id,
                    'reactable_type' => $reactable->getMorphClass(),
                    'reactable_id' => $reactable->getKey(),
                    'type' => $type,
                ]);
            }

            $this->refreshCount($reactable);

            return $this->summary($reactable, $resultType);
        });
    }

    /**
     * @return array{type: null, counts: array<string, int>, total: int}
     */
    public function remove(Model $reactable, User $user): array
    {
        return DB::transaction(function () use ($reactable, $user) {
            Reaction::query()
                ->where('user_id', $user->id)
                ->where('reactable_type', $reactable->getMorphClass())
                ->where('reactable_id', $reactable->getKey())
                ->delete();

            $this->refreshCount($reactable);

            return $this->summary($reactable, null);
        });
    }

    public function refreshCount(Model $reactable): void
    {
        if ($reactable instanceof Experience) {
            $total = Reaction::query()
                ->where('reactable_type', $reactable->getMorphClass())
                ->where('reactable_id', $reactable->getKey())
                ->count();
            $reactable->update(['reaction_count' => $total]);
        }
    }

    /**
     * @return array{type: string|null, counts: array<string, int>, total: int}
     */
    public function summary(Model $reactable, ?string $userType = null): array
    {
        $rows = Reaction::query()
            ->where('reactable_type', $reactable->getMorphClass())
            ->where('reactable_id', $reactable->getKey())
            ->selectRaw('type, COUNT(*) as cnt')
            ->groupBy('type')
            ->pluck('cnt', 'type');

        $counts = [
            'like' => (int) ($rows[ReactionType::Like->value] ?? 0),
            'love' => (int) ($rows[ReactionType::Love->value] ?? 0),
        ];

        return [
            'type' => $userType,
            'counts' => $counts,
            'total' => array_sum($counts),
        ];
    }
}
