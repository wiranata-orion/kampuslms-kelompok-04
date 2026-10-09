<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\Material;
use App\Models\User;

class MaterialPolicy
{
    public function viewAny(User $user, Course $course): bool
    {
        return $this->canViewCourse($user, $course);
    }

    public function view(User $user, Material $material): bool
    {
        return $this->canViewCourse($user, $material->course);
    }

    public function create(User $user, Course $course): bool
    {
        return $user->role === 'admin'
            || ($user->role === 'dosen' && $course->lecturer_id === $user->id);
    }

    public function update(User $user, Material $material): bool
    {
        return $this->canManageCourse($user, $material->course);
    }

    public function delete(User $user, Material $material): bool
    {
        return $this->canManageCourse($user, $material->course);
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
