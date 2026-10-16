<?php

use App\Http\Controllers\Api\AssignmentApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseApiController;
use App\Http\Controllers\Api\DirectResetPasswordController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PortalController;
use App\Http\Controllers\Api\SubmissionController;
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
        Route::patch('/me', [PortalController::class, 'updateCurrentUser'])->name('me.update');

        // Dashboard
        Route::get('/dashboard', [PortalController::class, 'dashboard'])->name('dashboard');

        // Notifikasi
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::post('/{notification}/read', [NotificationController::class, 'read'])->name('read');
            Route::post('/read-all', [NotificationController::class, 'readAll'])->name('read-all');
        });

        // ── Grup Rute: Admin ─────────────────────────────────────────────────
        Route::middleware('role:admin')->group(function () {
            Route::get('/users', [PortalController::class, 'users'])->name('users.index');
            Route::post('/users', [PortalController::class, 'storeUser'])->name('users.store');
            Route::delete('/users/{user}', [PortalController::class, 'destroyUser'])->name('users.destroy');
            Route::get('/lecturers', [PortalController::class, 'lecturers'])->name('lecturers.index');

            Route::post('/courses', [PortalController::class, 'storeCourse'])->name('courses.store');
            Route::put('/courses/{course}', [PortalController::class, 'updateCourse'])->name('courses.update');
            Route::delete('/courses/{course}', [PortalController::class, 'destroyCourse'])->name('courses.destroy');
        });

        // User profile & edit (Admin + Diri sendiri via Policy)
        Route::get('/users/{user}', [PortalController::class, 'showUser'])->name('users.show');
        Route::put('/users/{user}', [PortalController::class, 'updateUser'])->name('users.update');

        // ── Grup Rute: Dosen & Admin (Penilaian) ──────────────────────────────
        Route::middleware('role:dosen,admin')->group(function () {
            Route::put('/submissions/{submission}/grade', [SubmissionController::class, 'grade'])->name('submissions.grade');
            Route::post('/grades/{grade}/publish', [SubmissionController::class, 'publishGrade'])->name('grades.publish');
        });

        // ── Course & Scoped Resources (Nested + scopeBindings) ───────────────
        Route::get('/my/courses', [CourseApiController::class, 'index'])->defaults('scope', 'my')->name('my.courses');
        Route::get('/courses', [CourseApiController::class, 'index'])->name('courses.index');
        Route::get('/courses/{course}', [CourseApiController::class, 'show'])->name('courses.show');

        Route::scopeBindings()->group(function () {
            // Enrollment management (admin & course lecturer via policy)
            Route::get('/courses/{course}/enrollments/candidates', [PortalController::class, 'enrollmentCandidates'])->name('courses.enrollments.candidates');
            Route::get('/courses/{course}/students', [PortalController::class, 'courseStudents'])->name('courses.students');
            Route::post('/courses/{course}/enrollments', [PortalController::class, 'storeEnrollment'])->name('courses.enrollments.store');
            Route::delete('/courses/{course}/enrollments/{user}', [PortalController::class, 'destroyEnrollment'])->name('courses.enrollments.destroy');

            // Course materials & assignments list
            Route::get('/courses/{course}/materials', [CourseApiController::class, 'materials'])->name('courses.materials');
            Route::post('/courses/{course}/materials', [PortalController::class, 'storeMaterial'])->name('materials.store');
            Route::get('/courses/{course}/assignments', [CourseApiController::class, 'assignments'])->name('courses.assignments');

            // Submissions nested under assignments
            Route::get('/assignments/{assignment}/submissions', [AssignmentApiController::class, 'submissions'])->name('assignments.submissions');
            Route::post('/assignments/{assignment}/submissions', [AssignmentApiController::class, 'submit'])->name('assignments.submit');
        });

        // Materials shallow routes
        Route::get('/materials/{material}', [PortalController::class, 'showMaterial'])->name('materials.show');
        Route::get('/materials/{material}/download', [PortalController::class, 'downloadMaterial'])->name('materials.download');
        Route::put('/materials/{material}', [PortalController::class, 'updateMaterial'])->name('materials.update');
        Route::delete('/materials/{material}', [PortalController::class, 'destroyMaterial'])->name('materials.destroy');

        // Assignments shallow routes
        Route::post('/assignments', [AssignmentApiController::class, 'store'])->name('assignments.store');
        Route::match(['put', 'patch'], '/assignments/{assignment}', [AssignmentApiController::class, 'update'])->name('assignments.update');
        Route::get('/assignments/{assignment}', [AssignmentApiController::class, 'show'])->name('assignments.show');
        Route::delete('/assignments/{assignment}', [AssignmentApiController::class, 'destroy'])->name('assignments.destroy');

        // Submissions & Grades
        Route::get('/my/submissions', [SubmissionController::class, 'mine'])->name('submissions.mine');
        Route::get('/submissions/{submission}', [SubmissionController::class, 'show'])->name('submissions.show');
        Route::put('/submissions/{submission}', [SubmissionController::class, 'updateMine'])->name('submissions.update');
        Route::get('/submissions/{submission}/download', [SubmissionController::class, 'download'])->name('submissions.download');
        Route::get('/submissions/{submission}/grade', [SubmissionController::class, 'studentGrade'])->name('submissions.student-grade');
        Route::get('/grades/{grade}', [SubmissionController::class, 'showGrade'])->name('grades.show');
    });
});
