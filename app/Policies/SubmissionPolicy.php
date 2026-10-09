<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\Submission;
use App\Models\User;

class SubmissionPolicy
{
    public function viewAny(User $user, ?Assignment $assignment = null): bool
    {
        if ($assignment === null) {
            return $user->role === 'mahasiswa';
        }

        return $user->role === 'admin'
            || ($user->role === 'dosen' && $assignment->course->lecturer_id === $user->id);
    }

    public function view(User $user, Submission $submission): bool
    {
        return $user->role === 'admin'
            || ($user->role === 'dosen'
                && $submission->assignment->course->lecturer_id === $user->id)
            || ($user->role === 'mahasiswa' && $submission->user_id === $user->id);
    }

    public function create(User $user, Assignment $assignment): bool
    {
        return $user->role === 'mahasiswa'
            && $assignment->status === 'published'
            && $assignment->course->status === 'active'
            && $assignment->course->students()->whereKey($user->id)->exists()
            && ! $assignment->submissions()->where('user_id', $user->id)->exists();
    }

    public function update(User $user, Submission $submission): bool
    {
        return false;
    }

    public function delete(User $user, Submission $submission): bool
    {
        return $user->role === 'admin'
            || ($user->role === 'dosen'
                && $submission->assignment->course->lecturer_id === $user->id);
    }
}
