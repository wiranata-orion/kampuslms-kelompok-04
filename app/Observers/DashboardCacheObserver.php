<?php

namespace App\Observers;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use App\Models\User;
use App\Support\DashboardCache;
use Illuminate\Database\Eloquent\Model;

/**
 * Membersihkan cache stats dashboard saat data sumbernya berubah.
 *
 * Catatan:
 * - cascadeOnDelete: anak dihapus database tanpa event, jadi id mahasiswa terdampak
 *   dicatat di "deleting" (sebelum cascade), lalu dibersihkan di "deleted".
 * - Pendaftaran mahasiswa (attach/detach pivot) tidak memicu event model; dibersihkan manual
 *   di CourseEnrollmentController.
 * - Hapus/ubah massal lewat query builder tidak memicu event; TTL di config/lms.php jadi batas basi.
 */
class DashboardCacheObserver
{
    /** @var array<string, array<int, int|string>> */
    private static array $pendingStudents = [];

    public function created(Model $model): void
    {
        if ($model instanceof User) {
            DashboardCache::forgetAdmin();
        } elseif ($model instanceof Course) {
            DashboardCache::forgetAdmin();
            DashboardCache::forgetLecturer($model->lecturer_id);
        } elseif ($model instanceof Assignment) {
            DashboardCache::forgetLecturer($this->lecturerIdOf($model));
        } elseif ($model instanceof Submission) {
            DashboardCache::forgetStudent($model->user_id);
        }
    }

    public function updated(Model $model): void
    {
        if ($model instanceof Course && $model->wasChanged('lecturer_id')) {
            DashboardCache::forgetLecturer($model->getOriginal('lecturer_id'));
            DashboardCache::forgetLecturer($model->lecturer_id);
        }
    }

    public function deleting(Model $model): void
    {
        if ($model instanceof Assignment) {
            self::$pendingStudents[$this->key($model)] = $model->submissions()->pluck('user_id')->all();
        } elseif ($model instanceof Course) {
            $enrolled = $model->students()->pluck('users.id')->all();
            $submitters = Submission::query()
                ->whereIn('assignment_id', $model->assignments()->select('assignments.id'))
                ->pluck('user_id')
                ->all();

            self::$pendingStudents[$this->key($model)] = array_merge($enrolled, $submitters);
        }
    }

    public function deleted(Model $model): void
    {
        if ($model instanceof User) {
            DashboardCache::forgetAdmin();
            DashboardCache::forgetLecturer($model->id);
            DashboardCache::forgetStudent($model->id);
        } elseif ($model instanceof Course) {
            DashboardCache::forgetAdmin();
            DashboardCache::forgetLecturer($model->lecturer_id);
            DashboardCache::forgetStudents($this->pull($model));
        } elseif ($model instanceof Assignment) {
            DashboardCache::forgetLecturer($this->lecturerIdOf($model));
            DashboardCache::forgetStudents($this->pull($model));
        } elseif ($model instanceof Submission) {
            DashboardCache::forgetStudent($model->user_id);
        }
    }

    public function restored(Model $model): void
    {
        if ($model instanceof User) {
            DashboardCache::forgetAdmin();
        }
    }

    private function lecturerIdOf(Assignment $assignment): ?int
    {
        $id = Course::query()->whereKey($assignment->course_id)->value('lecturer_id');

        return $id === null ? null : (int) $id;
    }

    private function key(Model $model): string
    {
        return $model::class.':'.$model->getKey();
    }

    /** @return array<int, int|string> */
    private function pull(Model $model): array
    {
        $ids = self::$pendingStudents[$this->key($model)] ?? [];
        unset(self::$pendingStudents[$this->key($model)]);

        return $ids;
    }
}
