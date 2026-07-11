<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CommentStatus;
use App\Models\Comment;
use App\Models\Experience;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CommentService
{
    /**
     * @param  array{body: string, rating?: int|null, parent_id?: int|null}  $data
     */
    public function create(Experience $experience, User $user, array $data): Comment
    {
        if (! empty($data['parent_id'])) {
            $parent = Comment::query()->find($data['parent_id']);
            if (! $parent || $parent->experience_id !== $experience->id) {
                throw ValidationException::withMessages([
                    'parent_id' => [__('validation.exists', ['attribute' => 'parent_id'])],
                ]);
            }
            if ($parent->parent_id !== null) {
                throw ValidationException::withMessages([
                    'parent_id' => [__('validation.comment_one_level')],
                ]);
            }
        }

        return DB::transaction(function () use ($experience, $user, $data) {
            $comment = Comment::query()->create([
                'experience_id' => $experience->id,
                'user_id' => $user->id,
                'parent_id' => $data['parent_id'] ?? null,
                'body' => $data['body'],
                'rating' => $data['rating'] ?? null,
                'status' => CommentStatus::Visible,
            ]);

            if (isset($data['rating'])) {
                $this->recalculateRating($experience);
            }

            return $comment->load('user');
        });
    }

    public function delete(Comment $comment): void
    {
        DB::transaction(function () use ($comment) {
            $experience = $comment->experience;
            $hadRating = $comment->rating !== null;
            $comment->delete();
            if ($hadRating && $experience) {
                $this->recalculateRating($experience);
            }
        });
    }

    public function updateStatus(Comment $comment, CommentStatus $status): Comment
    {
        $comment->update(['status' => $status]);

        return $comment->fresh('user');
    }

    public function listForExperience(Experience $experience, int $perPage = 15): LengthAwarePaginator
    {
        return $experience->comments()
            ->whereNull('parent_id')
            ->where('status', CommentStatus::Visible)
            ->with(['user', 'replies' => fn ($q) => $q->where('status', CommentStatus::Visible)->with('user')])
            ->latest()
            ->paginate(min($perPage, 50));
    }

    public function recalculateRating(Experience $experience): void
    {
        $stats = $experience->comments()
            ->whereNotNull('rating')
            ->where('status', CommentStatus::Visible)
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as cnt')
            ->first();

        $experience->update([
            'rating_avg' => round((float) ($stats->avg_rating ?? 0), 2),
            'rating_count' => (int) ($stats->cnt ?? 0),
        ]);
    }
}
