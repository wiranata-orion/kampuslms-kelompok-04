<?php

namespace App\Observers;

use App\Models\Course;
use App\Models\Submission;
use App\Support\PrivateFiles;

/**
 * materials.course_id dan assignments.course_id memakai cascadeOnDelete(), lalu
 * submissions.assignment_id juga cascade. Semua anak dihapus oleh database tanpa
 * event, jadi path berkas materi dan submission dicatat dulu di "deleting".
 */
class CourseObserver
{
    /** @var array<int|string, array<int, string>> */
    private array $pending = [];

    public function deleting(Course $course): void
    {
        $materialPaths = $course->materials()
            ->whereNotNull('file_path')
            ->pluck('file_path')
            ->all();

        $submissionPaths = Submission::query()
            ->whereIn('assignment_id', $course->assignments()->select('assignments.id'))
            ->pluck('file_path')
            ->all();

        $this->pending[$course->getKey()] = array_merge($materialPaths, $submissionPaths);
    }

    public function deleted(Course $course): void
    {
        $paths = $this->pending[$course->getKey()] ?? [];
        unset($this->pending[$course->getKey()]);

        PrivateFiles::deleteAfterCommit($paths);
    }
}
