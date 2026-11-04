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
        $submission->loadMissing('assignment.course');

        return $user->role === 'admin'
            || ($user->role === 'dosen'
                && $submission->assignment->course->lecturer_id === $user->id)
            || ($user->role === 'mahasiswa' && $submission->user_id === $user->id);
    }

    public function download(User $user, Submission $submission): bool
    {
        return $this->view($user, $submission);
    }

    public function create(User $user, Assignment $assignment): bool
    {
        return $user->role === 'mahasiswa'
            && $assignment->status === 'published'
            && $assignment->course->status === 'active'
            && $assignment->course->students()->whereKey($user->id)->exists();
    }

    public function update(User $user, Submission $submission): bool
    {
        $submission->loadMissing('assignment.course');
        $course = $submission->assignment->course;

        return $user->role === 'mahasiswa'
            && $submission->user_id === $user->id
            && $submission->assignment->status === 'published'
            && $course->status === 'active'
            && ($course->relationLoaded('students')
                ? $course->students->contains('id', $user->getKey())
                : $course->students()->whereKey($user->getKey())->exists())
            && ! ($submission->relationLoaded('grade')
                ? $submission->getRelation('grade') !== null
                : $submission->grade()->exists());
    }

    public function delete(User $user, Submission $submission): bool
    {
        $submission->loadMissing('assignment.course');

        return $user->role === 'admin'
            || ($user->role === 'dosen'
                && $submission->assignment->course->lecturer_id === $user->id);
    }
}
