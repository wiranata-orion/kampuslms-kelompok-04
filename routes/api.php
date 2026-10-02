<?php

use App\Http\Controllers\Api\AssignmentApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseApiController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\SubmissionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->middleware('throttle:60,1')->group(function () {
    // Guest / Public Auth Routes
    Route::post('/auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:100,1')
        ->name('login');

    // Authenticated Routes
    Route::middleware('auth:sanctum')->group(function () {
        // Auth Management
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/me', [AuthController::class, 'me'])->name('me');

        // Course Endpoints
        Route::get('/courses', [CourseApiController::class, 'index'])->name('courses.index');
        Route::get('/courses/{course}', [CourseApiController::class, 'show'])->name('courses.show');
        Route::get('/courses/{course}/materials', [CourseApiController::class, 'materials'])->name('courses.materials');
        Route::get('/courses/{course}/assignments', [CourseApiController::class, 'assignments'])->name('courses.assignments');

        // Assignment Endpoints
        Route::post('/assignments', [AssignmentApiController::class, 'store'])->name('assignments.store');
        Route::match(['put', 'patch'], '/assignments/{assignment}', [AssignmentApiController::class, 'update'])->name('assignments.update');
        Route::delete('/assignments/{assignment}', [AssignmentApiController::class, 'destroy'])->name('assignments.destroy');
        
        // Submissions & Grading Endpoints
        Route::get('/assignments/{assignment}/submissions', [AssignmentApiController::class, 'submissions'])->name('assignments.submissions');
        Route::post('/assignments/{assignment}/submissions', [AssignmentApiController::class, 'submit'])->name('assignments.submit');
        Route::put('/submissions/{submission}/grade', [SubmissionController::class, 'grade'])->name('submissions.grade');

        // Notification Endpoints
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    });
});