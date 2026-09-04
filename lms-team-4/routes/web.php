<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;

Route::get('/about', function () {
    return view('about');
});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/tentang', function () {
    return view('tentang');
});

Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');

Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
