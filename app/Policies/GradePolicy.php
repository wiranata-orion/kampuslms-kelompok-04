<?php

namespace App\Policies;

use App\Models\Grade;
use App\Models\Submission;
use App\Models\User;

class GradePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'dosen'], true);
    }

    public function view(User $user, Grade $grade): bool
    {
        return $user->role === 'admin'
            || ($user->role === 'dosen'
                && $grade->submission->assignment->course->lecturer_id === $user->id)
            || ($user->role === 'mahasiswa'
                && $grade->submission->user_id === $user->id
                && $grade->published_at !== null);
    }

    public function create(User $user, Submission $submission): bool
    {
        return $user->role === 'dosen'
            && $submission->assignment->course->lecturer_id === $user->id;
    }

    public function update(User $user, Grade $grade): bool
    {
        return $user->role === 'dosen'
            && $grade->submission->assignment->course->lecturer_id === $user->id;
    }

    public function delete(User $user, Grade $grade): bool
    {
        return $user->role === 'admin'
            || ($user->role === 'dosen'
                && $grade->submission->assignment->course->lecturer_id === $user->id);
    }

    public function publish(User $user, Grade $grade): bool
    {
        return $user->role === 'dosen'
            && $grade->submission->assignment->course->lecturer_id === $user->id;
    }
}
