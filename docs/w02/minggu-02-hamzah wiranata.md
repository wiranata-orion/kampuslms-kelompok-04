Nama : Hamzah Wiranata
Nim : 10241035
Kelas : A

### READ — Telusuri satu request penuh (30 menit)

Ambil route `/tentang` yang Anda buat minggu lalu. Tanpa AI, tulis di catatan Anda:

1. Baris mana di `routes/web.php` yang menangkapnya?
2. Kalau ditangani controller, berkas dan method mana?
3. View mana yang dikembalikan? Di path apa persisnya?
4. Layout apa yang membungkusnya?
5. Jalankan `php artisan route:list --path=tentang`. Cocok dengan analisis Anda?

Jawaban

1. Pada Baris
    ```php
    Route::get('/tentang', function () {
        return view('tentang');
    });
    ```
2. Jika ditangani controller, berkasnya ada di `app/Http/Controllers/AboutController.php` dan memanggil controller misal `Route::get('/tentang', [HomeController::class, 'tentang'])->name('tentang.index');
` dengan method misal `tentang()`.
3. View yang dikembalikan adalah `tentang.blade.php` yang berada di path `resources/views/tentang.blade.php`.
4. Layout yang membungkusnya adalah `<x-layout>` yang berada di path `resources/views/components/layout.blade.php`.
5. `php artisan route:list --path=tentang` menghasilkan output yang sama dengan analisis saya yaitu satu route karena tentang hanya saya buat 1 mungkin jika saya buat group pada route tentang maka hasilnya akan lebih dari satu
