<?php

use App\Http\Controllers\Admin\EnrollmentController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| A. Auth (prasyarat — belum ada di kode saat ini)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| B. Umum — semua peran yang sudah login
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Katalog mata kuliah: index terbuka untuk semua peran,
    // tapi show() WAJIB diotorisasi di controller/policy — mahasiswa
    // hanya boleh melihat course yang dia ikuti (lihat catatan di bawah).
    Route::resource('courses', CourseController::class)->only(['index', 'show']);

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
});

/*
|--------------------------------------------------------------------------
| C. Admin
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {

        Route::resource('users', UserController::class);

        // index & show sudah didefinisikan di grup Umum, jadi dikecualikan di sini
        Route::resource('courses', CourseController::class)->except(['index', 'show']);

        // Enrollment: index/create/store butuh konteks {course}, destroy tidak (shallow)
        Route::resource('courses.enrollments', EnrollmentController::class)
            ->shallow()
            ->only(['index', 'create', 'store', 'destroy']);
    });

/*
|--------------------------------------------------------------------------
| D. Dosen
|--------------------------------------------------------------------------
*/
Route::prefix('dosen')
    ->name('dosen.')
    ->middleware(['auth', 'role:dosen'])
    ->group(function () {

        Route::get('/courses', [CourseController::class, 'myCourses'])->name('courses.index');

        // Materi: shallow — index/create/store butuh {course}, sisanya cukup {material}
        Route::resource('courses.materials', MaterialController::class)->shallow();

        // Tugas: shallow — sama pola dengan materi
        Route::resource('courses.assignments', AssignmentController::class)->shallow();

        // Pengumpulan tugas: dosen hanya membaca untuk menilai, tidak CRUD penuh
        Route::get('/assignments/{assignment}/submissions', [SubmissionController::class, 'indexForDosen'])
            ->name('assignments.submissions.index');
        Route::get('/submissions/{submission}', [SubmissionController::class, 'showForDosen'])
            ->name('submissions.show');

        // Nilai: nested di submission (relasi 1:1), shallow agar edit/update cukup {grade}
        Route::resource('submissions.grade', GradeController::class)
            ->shallow()
            ->parameters(['grade' => 'grade'])
            ->only(['create', 'store', 'edit', 'update']);
    });

/*
|--------------------------------------------------------------------------
| E. Mahasiswa
|--------------------------------------------------------------------------
*/
Route::prefix('mahasiswa')
    ->name('mahasiswa.')
    ->middleware(['auth', 'role:mahasiswa'])
    ->group(function () {

        Route::get('/courses', [CourseController::class, 'myCourses'])->name('courses.index');

        // Pengumpulan tugas: shallow, tanpa destroy (sesuai kesepakatan)
        Route::resource('assignments.submissions', SubmissionController::class)
            ->shallow()
            ->only(['create', 'store', 'show', 'edit', 'update']);

        // Lihat nilai atas submission sendiri
        Route::get('/submissions/{submission}/grade', [GradeController::class, 'showForStudent'])
            ->name('submissions.grade.show');
    });