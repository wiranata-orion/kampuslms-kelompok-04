<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Data statis mata kuliah sebagai simulasi sebelum integrasi database.
     * Dibuat privat agar reusable di method index dan show.
     */
    private array $courses = [
        [
            'id' => 1,
            'kode' => 'SI101',
            'nama' => 'Analisis dan Desain Sistem',
            'sks' => 3,
            'semester' => 4,
            'dosen' => 'Budi Santoso, M.Kom.',
            'deskripsi' => 'Mempelajari metodologi pengembangan perangkat lunak, perancangan diagram UML, serta analisis kebutuhan bisnis.'
        ],
        [
            'id' => 2,
            'kode' => 'SI102',
            'nama' => 'Pemrograman Berorientasi Objek',
            'sks' => 4,
            'semester' => 2,
            'dosen' => 'Dewi Lestari, M.T.',
            'deskripsi' => 'Konsep dasar OOP, inheritance, polymorphism, encapsulation, serta implementasi MVC modern.'
        ],
        [
            'id' => 3,
            'kode' => 'SI103',
            'nama' => 'Manajemen Basis Data',
            'sks' => 3,
            'semester' => 3,
            'dosen' => 'Ahmad Fauzi, Ph.D.',
            'deskripsi' => 'Normalisasi basis data, relasi tabel, indexing, serta optimalisasi kueri relasional SQL.'
        ],
    ];

    /**
     * Menampilkan daftar semua mata kuliah.
     * Mengirimkan array courses ke view index.
     */
    public function index()
    {
        return view('courses.index', [
            'title' => 'Daftar Mata Kuliah',
            'courses' => $this->courses
        ]);
    }

    /**
     * Menampilkan detail satu mata kuliah berdasarkan parameter ID di URL.
     * Menggunakan abort(404) jika ID tidak ditemukan untuk keamanan routing.
     */
    public function show(string $id)
    {
        // Mencari elemen array berdasarkan kecocokan kolom 'id'
        $course = collect($this->courses)->firstWhere('id', (int) $id);

        // Jika data tidak ditemukan, hentikan proses dan tampilkan respon 404
        if (!$course) {
            abort(404, 'Mata kuliah tidak ditemukan.');
        }

        return view('courses.show', [
            'title' => 'Detail Mata Kuliah',
            'course' => $course
        ]);
    }
}