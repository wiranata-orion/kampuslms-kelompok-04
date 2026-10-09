<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;

class AssignmentPolicy
{
    public function viewAny(User $user, Course $course): bool
    {
        return $this->canViewCourse($user, $course);
    }

    public function view(User $user, Assignment $assignment): bool
    {
        $course = $assignment->course;

        return match ($user->role) {
            'admin' => true,
            'dosen' => $course->lecturer_id === $user->id,
            'mahasiswa' => $assignment->status === 'published'
                && $course->status === 'active'
                && $course->students()->whereKey($user->id)->exists(),
            default => false,
        };
    }

    public function createAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'dosen'], true);
    }

    public function create(User $user, Course $course): bool
    {
        return $user->role === 'admin'
            || ($user->role === 'dosen' && $course->lecturer_id === $user->id);
    }

    public function update(User $user, Assignment $assignment): bool
    {
        return $this->canManageCourse($user, $assignment->course);
    }

    public function delete(User $user, Assignment $assignment): bool
    {
        $attributes = $assignment->getAttributes();
        $hasGrades = array_key_exists('grades_count', $attributes)
            ? (int) $attributes['grades_count'] > 0
            : $assignment->grades()->exists();

        return $this->canManageCourse($user, $assignment->course)
            && ! $hasGrades;
    }

    private function canViewCourse(User $user, Course $course): bool
    {
        return match ($user->role) {
            'admin' => true,
            'dosen' => $course->lecturer_id === $user->id,
            'mahasiswa' => $course->status === 'active'
                && $course->students()->whereKey($user->id)->exists(),
            default => false,
        };
    }

    private function canManageCourse(User $user, Course $course): bool
    {
        return $user->role === 'admin'
            || ($user->role === 'dosen' && $course->lecturer_id === $user->id);
    }
}
