<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\UserController;

// Pengalihan dari halaman utama '/' langsung ke daftar mata kuliah
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard / halaman utama (sementara memakai file about.blade.php)
Route::view('/dashboard', 'about')->name('dashboard');

Route::get('/tentang', function () {
    return view('tentang');
});

// Group modul courses (prefix URI 'courses' dan prefix nama rute 'courses.')
Route::prefix('courses')->name('courses.')->group(function () {

    // 1. Menampilkan daftar semua mata kuliah (Admin, Dosen, Mahasiswa)
    Route::get('/', [CourseController::class, 'index'])->name('index');

    // 2. Menampilkan formulir tambah mata kuliah (Admin)
    // CATATAN: Wajib ditaruh SEBELUM /{course} agar kata 'create' tidak dianggap sebagai parameter {course}
    Route::get('/create', [CourseController::class, 'create'])->name('create');

    // 3. Menyimpan data mata kuliah baru ke database (Admin)
    Route::post('/', [CourseController::class, 'store'])->name('store');

    // 4. Menampilkan detail lengkap satu mata kuliah (Admin, Dosen, Mahasiswa)
    Route::get('/{course}', [CourseController::class, 'show'])->name('show');

    // 5. Menampilkan formulir edit mata kuliah (Admin, Dosen pengampu)
    Route::get('/{course}/edit', [CourseController::class, 'edit'])->name('edit');

    // 6. Memperbarui data mata kuliah yang diedit (Admin, Dosen pengampu)
    Route::match(['put', 'patch'], '/{course}', [CourseController::class, 'update'])->name('update');

    // 7. Menghapus data mata kuliah (Admin)
    Route::delete('/{course}', [CourseController::class, 'destroy'])->name('destroy');
});

// Group modul users (prefix URI 'users' dan prefix nama rute 'users.')
Route::prefix('users')->name('users.')->group(function () {

    // 1. Menampilkan daftar semua pengguna (Admin)
    Route::get('/', [UserController::class, 'index'])->name('index');

    // 2. Menampilkan formulir tambah pengguna (Admin)
    // CATATAN: Wajib ditaruh SEBELUM /{user} agar kata 'create' tidak dianggap sebagai parameter {user}
    Route::get('/create', [UserController::class, 'create'])->name('create');

    // 3. Menyimpan data pengguna baru ke database (Admin)
    Route::post('/', [UserController::class, 'store'])->name('store');

    // 4. Menampilkan detail lengkap satu pengguna (Admin)
    Route::get('/{user}', [UserController::class, 'show'])->name('show');

    // 5. Menampilkan formulir edit pengguna (Admin)
    Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');

    // 6. Memperbarui data pengguna yang diedit (Admin)
    Route::match(['put', 'patch'], '/{user}', [UserController::class, 'update'])->name('update');

    // 7. Menghapus data pengguna (Admin)
    Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
});

// Route::controller(CourseController::class)
//     ->prefix('courses')
//     ->name('courses.')
//     ->group(function () {
//         Route::get('/', 'index')->name('index');
//         Route::get('/{course}', 'show')->name('show');

//         // Belum ada method-nya di CourseController — aktifkan setelah dibuat.
//         // Selama masih di-comment, tombol "+ Tambah Mata Kuliah" di navbar
//         // otomatis tersembunyi berkat pengecekan Route::has() di layout.
//         // Route::get('/create', 'create')->name('create');
//         // Route::post('/', 'store')->name('store');
//         // Route::get('/{course}/edit', 'edit')->name('edit');
//         // Route::match(['put', 'patch'], '/{course}', 'update')->name('update');
//         // Route::delete('/{course}', 'destroy')->name('destroy');
//     });