<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

/**
 * Satu pintu untuk berkas privat (materi dan submission).
 * Selalu memakai disk privat, tidak pernah disk "public".
 */
final class PrivateFiles
{
    /** Disk privat: storage/app/private. */
    public const DISK = 'local';

    /** Hanya PDF yang boleh ditampilkan inline di browser. */
    public static function canPreview(string $path): bool
    {
        return strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf';
    }

    /**
     * Kirim berkas sebagai stream (unduhan atau pratinjau). Tidak ada URL permanen:
     * setiap permintaan harus melewati rute terautentikasi + Policy.
     */
    public static function response(string $path, ?string $name = null, bool $preview = false): StreamedResponse
    {
        $disk = Storage::disk(self::DISK);

        abort_unless($path !== '' && $disk->exists($path), 404, 'Berkas tidak ditemukan.');

        $filename = self::safeName($name ?: basename($path));
        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store, max-age=0',
        ];

        return $preview && self::canPreview($path)
            ? $disk->response($path, $filename, $headers, 'inline')
            : $disk->download($path, $filename, $headers);
    }

    /**
     * Hapus berkas fisik hanya setelah transaksi database commit.
     * Jika tidak ada transaksi, dijalankan langsung. Jika transaksi di-rollback,
     * penghapusan dibatalkan sehingga baris dan berkas tetap konsisten.
     *
     * @param  iterable<int, string|null>  $paths
     */
    public static function deleteAfterCommit(iterable $paths): void
    {
        $paths = collect($paths)
            ->filter(fn ($path) => is_string($path) && $path !== '')
            ->unique()
            ->values()
            ->all();

        if ($paths === []) {
            return;
        }

        DB::afterCommit(function () use ($paths): void {
            try {
                Storage::disk(self::DISK)->delete($paths);
            } catch (Throwable $e) {
                report($e);
            }
        });
    }

    private static function safeName(string $name): string
    {
        $clean = trim(preg_replace('/[\x00-\x1F\x7F\/\\\\]+/', '_', $name) ?? '');

        return $clean !== '' ? $clean : 'berkas';
    }
}
