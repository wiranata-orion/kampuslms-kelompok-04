<?php

use App\Http\Controllers\Api\AssignmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\CourseEnrollmentController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DirectResetPasswordController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\SubmissionController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->middleware('throttle:60,1')->group(function () {

    // ── Guest / Public Auth Routes ───────────────────────────────────────────
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('/check-user', [DirectResetPasswordController::class, 'checkUser'])->name('check-user');
        Route::post('/reset-password', [DirectResetPasswordController::class, 'resetPassword'])->name('reset-password');
        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:100,1')
            ->name('login');
    });

    // ── Authenticated Routes ─────────────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {
        // Auth & Self-management
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/me', [AuthController::class, 'me'])->name('me');
        Route::patch('/me', [UserController::class, 'updateCurrentUser'])->name('me.update');
        Route::post('/auth/change-password', [AuthController::class, 'changePassword'])->name('auth.change-password');

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Notifikasi
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::post('/{notification}/read', [NotificationController::class, 'read'])->name('read');
            Route::post('/read-all', [NotificationController::class, 'readAll'])->name('read-all');
        });

        // ── Grup Rute: Admin ─────────────────────────────────────────────────
        Route::middleware('role:admin')->group(function () {
            Route::get('/users', [UserController::class, 'index'])->name('users.index');
            Route::post('/users', [UserController::class, 'store'])->name('users.store');
            Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
            Route::get('/lecturers', [UserController::class, 'lecturers'])->name('lecturers.index');

            Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
            Route::put('/courses/{course}', [CourseController::class, 'update'])->name('courses.update');
            Route::delete('/courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');
        });

        // User profile & edit (Admin + Diri sendiri via Policy)
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');

        // ── Grup Rute: Dosen & Admin (Penilaian) ──────────────────────────────
        Route::middleware('role:dosen,admin')->group(function () {
            Route::put('/submissions/{submission}/grade', [SubmissionController::class, 'grade'])->name('submissions.grade');
            Route::put('/grades/{grade}', [SubmissionController::class, 'updateGrade'])->name('grades.update');
            Route::post('/grades/{grade}/publish', [SubmissionController::class, 'publishGrade'])->name('grades.publish');
        });

        // ── Course & Scoped Resources (Nested + scopeBindings) ───────────────
        Route::get('/my/courses', [CourseController::class, 'index'])->defaults('scope', 'my')->name('my.courses');
        Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
        Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');

        Route::scopeBindings()->group(function () {
            // Enrollment management (admin & course lecturer via policy)
            Route::get('/courses/{course}/enrollments/candidates', [CourseEnrollmentController::class, 'candidates'])->name('courses.enrollments.candidates');
            Route::get('/courses/{course}/students', [CourseEnrollmentController::class, 'students'])->name('courses.students');
            Route::post('/courses/{course}/enrollments', [CourseEnrollmentController::class, 'store'])->name('courses.enrollments.store');
            Route::delete('/courses/{course}/enrollments/{user}', [CourseEnrollmentController::class, 'destroy'])->name('courses.enrollments.destroy');

            // Course materials & assignments list
            Route::get('/courses/{course}/materials', [CourseController::class, 'materials'])->name('courses.materials');
            Route::post('/courses/{course}/materials', [MaterialController::class, 'store'])->name('materials.store');
            Route::get('/courses/{course}/assignments', [CourseController::class, 'assignments'])->name('courses.assignments');

            // Submissions nested under assignments
            Route::get('/assignments/{assignment}/submissions', [AssignmentController::class, 'submissions'])->name('assignments.submissions');
            Route::post('/assignments/{assignment}/submissions', [AssignmentController::class, 'submit'])->name('assignments.submit');
        });

        // Materials shallow routes
        Route::get('/materials/{material}', [MaterialController::class, 'show'])->name('materials.show');
        Route::get('/materials/{material}/download', [MaterialController::class, 'download'])->name('materials.download');
        Route::put('/materials/{material}', [MaterialController::class, 'update'])->name('materials.update');
        Route::delete('/materials/{material}', [MaterialController::class, 'destroy'])->name('materials.destroy');

        // Assignments shallow routes
        Route::post('/assignments', [AssignmentController::class, 'store'])->name('assignments.store');
        Route::match(['put', 'patch'], '/assignments/{assignment}', [AssignmentController::class, 'update'])->name('assignments.update');
        Route::get('/assignments/{assignment}', [AssignmentController::class, 'show'])->name('assignments.show');
        Route::delete('/assignments/{assignment}', [AssignmentController::class, 'destroy'])->name('assignments.destroy');

        // Submissions & Grades
        Route::get('/my/submissions', [SubmissionController::class, 'mine'])->name('submissions.mine');
        Route::get('/submissions/{submission}', [SubmissionController::class, 'show'])->name('submissions.show');
        Route::put('/submissions/{submission}', [SubmissionController::class, 'updateMine'])->name('submissions.update');
        Route::get('/submissions/{submission}/download', [SubmissionController::class, 'download'])->name('submissions.download');
        Route::get('/submissions/{submission}/grade', [SubmissionController::class, 'studentGrade'])->name('submissions.student-grade');
        Route::get('/grades/{grade}', [SubmissionController::class, 'showGrade'])->name('grades.show');
    });
});
