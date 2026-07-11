<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Experience;
use App\Models\User;

class ExperiencePolicy
{
    public function view(?User $user, Experience $experience): bool
    {
        if ($experience->isPublished()) {
            return true;
        }

        return $user !== null && $user->id === $experience->user_id;
    }

    public function create(User $user): bool
    {
        return $user->isActive();
    }

    public function update(User $user, Experience $experience): bool
    {
        return $user->id === $experience->user_id && $user->isActive();
    }

    public function delete(User $user, Experience $experience): bool
    {
        return $user->id === $experience->user_id && $user->isActive();
    }
}
