<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CourseApiController;

Route::prefix('v1')->group(function () {

    // Authentication
    Route::post('/auth/login', function () {
        return response()->json([
            'message' => 'Login endpoint',
        ]);
    });

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/auth/logout', function () {
            return response()->json([
                'message' => 'Logout endpoint',
            ]);
        });

        Route::get('/me', function () {
            return response()->json([
                'message' => 'Me endpoint',
            ]);
        });

        // Courses
        Route::get('/courses', [CourseApiController::class, 'index']);

        Route::get('/courses/{course}', function () {
            return response()->json([
                'message' => 'Course detail endpoint',
            ]);
        });

        Route::get('/courses/{course}/materials', function () {
            return response()->json([
                'message' => 'Materials endpoint',
            ]);
        });

        Route::get('/courses/{course}/assignments', function () {
            return response()->json([
                'message' => 'Course assignments endpoint',
            ]);
        });

        // Assignments
        Route::post('/assignments', function () {
            return response()->json([
                'message' => 'Create assignment endpoint',
            ]);
        });

        Route::put('/assignments/{assignment}', function () {
            return response()->json([
                'message' => 'Update assignment endpoint',
            ]);
        });

        Route::delete('/assignments/{assignment}', function () {
            return response()->json([
                'message' => 'Delete assignment endpoint',
            ]);
        });

        Route::get('/assignments/{assignment}/submissions', function () {
            return response()->json([
                'message' => 'Submissions endpoint',
            ]);
        });

        // Submissions
        Route::post('/assignments/{assignment}/submissions', function () {
            return response()->json([
                'message' => 'Create submission endpoint',
            ]);
        });

        Route::put('/submissions/{submission}/grade', function () {
            return response()->json([
                'message' => 'Grade endpoint',
            ]);
        });

        // Notifications
        Route::get('/notifications', function () {
            return response()->json([
                'message' => 'Notifications endpoint',
            ]);
        });

        Route::post('/notifications/{notification}/read', function () {
            return response()->json([
                'message' => 'Read notification endpoint',
            ]);
        });
    });
});