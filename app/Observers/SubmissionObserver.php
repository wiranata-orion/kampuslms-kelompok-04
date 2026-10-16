<?php

namespace App\Observers;

use App\Models\Submission;
use App\Support\PrivateFiles;

class SubmissionObserver
{
    /**
     * Mahasiswa mengumpulkan ulang (updateOrCreate menimpa baris lama):
     * berkas lama dihapus setelah baris berhasil diperbarui.
     */
    public function updated(Submission $submission): void
    {
        if ($submission->wasChanged('file_path')) {
            PrivateFiles::deleteAfterCommit([$submission->getOriginal('file_path')]);
        }
    }

    public function deleted(Submission $submission): void
    {
        PrivateFiles::deleteAfterCommit([$submission->file_path]);
    }
}
