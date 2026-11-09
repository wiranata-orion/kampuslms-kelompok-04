<?php

namespace App\Support;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Cache untuk blok "stats" dashboard (hanya angka agregat).
 * Blok "user" TIDAK pernah di-cache, dan otorisasi tetap berjalan di setiap request.
 *
 * Store "database" dan "file" tidak mendukung tags, jadi invalidasi memakai key eksplisit.
 */
final class DashboardCache
{
    public static function adminKey(): string
    {
        return 'dashboard:admin:stats';
    }

    public static function lecturerKey(int $id): string
    {
        return "dashboard:dosen:{$id}:stats";
    }

    public static function studentKey(int $id): string
    {
        return "dashboard:mahasiswa:{$id}:stats";
    }

    /** @return array<string, int> */
    public static function stats(User $user): array
    {
        return match ($user->role) {
            'admin' => Cache::remember(self::adminKey(), self::ttl('admin'), fn (): array => [
                'total_users' => User::count(),
                'total_courses' => Course::count(),
            ]),
            'dosen' => Cache::remember(self::lecturerKey($user->id), self::ttl('dosen'), fn (): array => [
                'total_courses' => $user->taughtCourses()->count(),
                'total_assignments' => Assignment::query()
                    ->whereIn('course_id', $user->taughtCourses()->select('courses.id'))
                    ->count(),
            ]),
            'mahasiswa' => Cache::remember(self::studentKey($user->id), self::ttl('mahasiswa'), fn (): array => [
                'total_courses' => $user->courses()->count(),
                'total_submissions' => $user->submissions()->count(),
            ]),
            default => [],
        };
    }

    public static function forgetAdmin(): void
    {
        self::forget(self::adminKey());
    }

    public static function forgetLecturer(?int $id): void
    {
        if ($id) {
            self::forget(self::lecturerKey($id));
        }
    }

    public static function forgetStudent(?int $id): void
    {
        if ($id) {
            self::forget(self::studentKey($id));
        }
    }

    /** @param iterable<int, int|string|null> $ids */
    public static function forgetStudents(iterable $ids): void
    {
        foreach (collect($ids)->filter()->unique() as $id) {
            self::forgetStudent((int) $id);
        }
    }

    /**
     * Dihapus setelah commit. Jika dihapus di tengah transaksi, request lain bisa mengisi
     * ulang cache dengan data lama sebelum commit. Jika transaksi di-rollback, cache dibiarkan.
     */
    private static function forget(string $key): void
    {
        DB::afterCommit(fn () => Cache::forget($key));
    }

    private static function ttl(string $role): int
    {
        return (int) config("lms.cache.dashboard_ttl.{$role}", 300);
    }
}
