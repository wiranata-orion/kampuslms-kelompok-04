<?php

namespace App\Observers;

use App\Models\Material;
use App\Support\PrivateFiles;

class MaterialObserver
{
    /**
     * Berkas diganti, atau materi diubah menjadi tipe link: hapus berkas lama.
     * Memakai "updated" (bukan "updating") agar berkas tidak terhapus
     * sebelum query UPDATE benar-benar berhasil.
     */
    public function updated(Material $material): void
    {
        if ($material->wasChanged('file_path')) {
            PrivateFiles::deleteAfterCommit([$material->getOriginal('file_path')]);
        }
    }

    public function deleted(Material $material): void
    {
        PrivateFiles::deleteAfterCommit([$material->file_path]);
    }
}
