<?php

namespace App\Observers;

use App\Models\Assignment;
use App\Support\PrivateFiles;

/**
 * submissions.assignment_id memakai cascadeOnDelete(): database menghapus baris
 * submission sendiri, sehingga SubmissionObserver::deleted() TIDAK terpicu.
 * Path berkas dicatat di "deleting" (sebelum cascade berjalan), lalu dihapus
 * di "deleted" setelah commit.
 */
class AssignmentObserver
{
    /** @var array<int|string, array<int, string>> */
    private array $pending = [];

    public function deleting(Assignment $assignment): void
    {
        $this->pending[$assignment->getKey()] = $assignment->submissions()
            ->pluck('file_path')
            ->all();
    }

    public function deleted(Assignment $assignment): void
    {
        $paths = $this->pending[$assignment->getKey()] ?? [];
        unset($this->pending[$assignment->getKey()]);

        PrivateFiles::deleteAfterCommit($paths);
    }
}
