<?php

use App\Http\Controllers\Api\AssignmentApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseApiController;
use App\Http\Controllers\Api\CourseEnrollmentController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DirectResetPasswordController;
use App\Http\Controllers\Api\GradeSummaryController;
use App\Http\Controllers\Api\MaterialApiController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\SubmissionController;
use App\Http\Controllers\Api\UserApiController;
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
        Route::patch('/me', [UserApiController::class, 'updateCurrentUser'])->name('me.update');
        Route::post('/auth/change-password', [AuthController::class, 'changePassword'])->name('auth.change-password');

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Notifikasi
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
            Route::post('/read-all', [NotificationController::class, 'readAll'])->name('read-all');
            Route::get('/{notification}', [NotificationController::class, 'show'])->name('show');
            Route::post('/{notification}/read', [NotificationController::class, 'read'])->name('read');
        });

        // ── Grup Rute: Admin ─────────────────────────────────────────────────
        Route::middleware('role:admin')->group(function () {
            Route::get('/users', [UserApiController::class, 'index'])->name('users.index');
            Route::post('/users', [UserApiController::class, 'store'])->name('users.store');
            Route::delete('/users/{user}', [UserApiController::class, 'destroy'])->name('users.destroy');
            Route::get('/lecturers', [UserApiController::class, 'lecturers'])->name('lecturers.index');

            Route::post('/courses', [CourseApiController::class, 'store'])->name('courses.store');
            Route::put('/courses/{course}', [CourseApiController::class, 'update'])->name('courses.update');
            Route::delete('/courses/{course}', [CourseApiController::class, 'destroy'])->name('courses.destroy');
        });

        // User profile & edit (Admin + Diri sendiri via Policy)
        Route::get('/users/{user}', [UserApiController::class, 'show'])->name('users.show');
        Route::put('/users/{user}', [UserApiController::class, 'update'])->name('users.update');

        // ── Grup Rute: Dosen & Admin (Penilaian) ──────────────────────────────
        Route::middleware('role:dosen,admin')->group(function () {
            Route::put('/submissions/{submission}/grade', [SubmissionController::class, 'grade'])->name('submissions.grade');
            Route::put('/grades/{grade}', [SubmissionController::class, 'updateGrade'])->name('grades.update');
            Route::post('/grades/{grade}/publish', [SubmissionController::class, 'publishGrade'])->name('grades.publish');
        });

        // ── Course & Scoped Resources (Nested + scopeBindings) ───────────────
        Route::get('/my/courses', [CourseApiController::class, 'index'])->defaults('scope', 'my')->name('my.courses');
        Route::get('/courses', [CourseApiController::class, 'index'])->name('courses.index');
        Route::get('/courses/{course}', [CourseApiController::class, 'show'])->name('courses.show');

        Route::scopeBindings()->group(function () {
            Route::get('/courses/{course}/grade-summary', [GradeSummaryController::class, 'course'])->name('courses.grades.summary');

            // Enrollment management (admin & course lecturer via policy)
            Route::get('/courses/{course}/enrollments/candidates', [CourseEnrollmentController::class, 'candidates'])->name('courses.enrollments.candidates');
            Route::get('/courses/{course}/students', [CourseEnrollmentController::class, 'students'])->name('courses.students');
            Route::post('/courses/{course}/enrollments', [CourseEnrollmentController::class, 'store'])->name('courses.enrollments.store');
            Route::delete('/courses/{course}/enrollments/{user}', [CourseEnrollmentController::class, 'destroy'])->name('courses.enrollments.destroy');

            // Course materials & assignments list
            Route::get('/courses/{course}/materials', [CourseApiController::class, 'materials'])->name('courses.materials');
            Route::post('/courses/{course}/materials', [MaterialApiController::class, 'store'])->name('materials.store');
            Route::get('/courses/{course}/assignments', [CourseApiController::class, 'assignments'])->name('courses.assignments');

            // Submissions nested under assignments
            Route::get('/assignments/{assignment}/submissions', [AssignmentApiController::class, 'submissions'])->name('assignments.submissions');
            Route::post('/assignments/{assignment}/submissions', [AssignmentApiController::class, 'submit'])->name('assignments.submit');
        });

        // Materials shallow routes
        Route::get('/materials/{material}', [MaterialApiController::class, 'show'])->name('materials.show');
        Route::get('/materials/{material}/download', [MaterialApiController::class, 'download'])->name('materials.download');
        Route::put('/materials/{material}', [MaterialApiController::class, 'update'])->name('materials.update');
        Route::delete('/materials/{material}', [MaterialApiController::class, 'destroy'])->name('materials.destroy');

        // Assignments shallow routes
        Route::post('/assignments', [AssignmentApiController::class, 'store'])->name('assignments.store');
        Route::match(['put', 'patch'], '/assignments/{assignment}', [AssignmentApiController::class, 'update'])->name('assignments.update');
        Route::get('/assignments/{assignment}', [AssignmentApiController::class, 'show'])->name('assignments.show');
        Route::delete('/assignments/{assignment}', [AssignmentApiController::class, 'destroy'])->name('assignments.destroy');

        // Submissions & Grades
        Route::get('/my/submissions', [SubmissionController::class, 'mine'])->name('submissions.mine');
        Route::get('/my/grade-summary', [GradeSummaryController::class, 'mine'])->name('grades.summary.mine');
        Route::get('/submissions/{submission}', [SubmissionController::class, 'show'])->name('submissions.show');
        Route::get('/submissions/{submission}/download', [SubmissionController::class, 'download'])->name('submissions.download');
        Route::get('/submissions/{submission}/grade', [SubmissionController::class, 'studentGrade'])->name('submissions.student-grade');
        Route::get('/grades/{grade}', [SubmissionController::class, 'showGrade'])->name('grades.show');
    });
});
