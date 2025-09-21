<?php

namespace App\Policies;

use App\Models\School;
use App\Models\User;

class SchoolPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, School $school): bool
    {
        return ($user->id === $school->user_id) || ($user->is_admin ?? false);
    }

    /**
     * Determine whether the user can create a school entry.
     * Business rule: one school per user.
     */
    public function create(User $user): bool
    {
        if ($user->is_admin ?? false) return true;
        return !$user->school()->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, School $school): bool
    {
        return ($user->id === $school->user_id) || ($user->is_admin ?? false);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, School $school): bool
    {
        return ($user->id === $school->user_id) || ($user->is_admin ?? false);
    }
}
