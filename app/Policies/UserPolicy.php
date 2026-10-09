<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function view(User $user, User $target): bool
    {
        return $user->role === 'admin' || $user->is($target);
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, User $target, array $attributes = []): bool
    {
        if ($user->role !== 'admin' && ! $user->is($target)) {
            return false;
        }

        return ! ($user->is($target) && array_key_exists('role', $attributes));
    }

    public function delete(User $user, User $target): bool
    {
        if ($user->role !== 'admin' || $user->is($target)) {
            return false;
        }

        return $target->role !== 'dosen' || ! $target->taughtCourses()->exists();
    }

    public function restore(User $user, User $target): bool
    {
        return $user->role === 'admin';
    }
}
