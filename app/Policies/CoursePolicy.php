<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'dosen', 'mahasiswa'], true);
    }

    public function view(User $user, Course $course): bool
    {
        return match ($user->role) {
            'admin' => true,
            'dosen' => $course->lecturer_id === $user->id,
            'mahasiswa' => $course->status === 'active'
                && $course->students()->whereKey($user->id)->exists(),
            default => false,
        };
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, Course $course): bool
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, Course $course): bool
    {
        return $user->role === 'admin'
            && ! $course->students()->exists()
            && ! $course->materials()->exists()
            && ! $course->assignments()->exists();
    }

    public function manageEnrollment(User $user, Course $course): bool
    {
        return $user->role === 'admin'
            || ($user->role === 'dosen' && $course->lecturer_id === $user->id);
    }
}
