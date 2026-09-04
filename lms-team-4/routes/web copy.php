<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;

// Pengalihan dari halaman utama '/' langsung ke daftar mata kuliah
Route::get('/', function () {
    return redirect()->route('courses.index');
});

// Group modul courses (prefix URI 'courses' dan prefix nama rute 'courses.')
Route::prefix('courses')->name('courses.')->group(function () {

    // 1. Menampilkan daftar semua mata kuliah (Admin, Dosen, Mahasiswa)
    Route::get('/', [CourseController::class, 'index'])->name('index');

    // 2. Menampilkan formulir tambah mata kuliah (Admin)
    // CATATAN: Wajib ditaruh SEBELUM /{id} agar kata 'create' tidak dianggap sebagai parameter {id}
    Route::get('/create', [CourseController::class, 'create'])->name('create');

    // 3. Menyimpan data mata kuliah baru ke database (Admin)
    Route::post('/', [CourseController::class, 'store'])->name('store');

    // 4. Menampilkan detail lengkap satu mata kuliah (Admin, Dosen, Mahasiswa)
    Route::get('/{id}', [CourseController::class, 'show'])->name('show');

    // 5. Menampilkan formulir edit mata kuliah (Admin, Dosen pengampu)
    Route::get('/{id}/edit', [CourseController::class, 'edit'])->name('edit');

    // 6. Memperbarui data mata kuliah yang diedit (Admin, Dosen pengampu)
    Route::match(['put', 'patch'], '/{id}', [CourseController::class, 'update'])->name('update');

    // 7. Menghapus data mata kuliah (Admin)
    Route::delete('/{id}', [CourseController::class, 'destroy'])->name('destroy');
});