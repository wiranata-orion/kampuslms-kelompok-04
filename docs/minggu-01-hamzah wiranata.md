# Catatan Minggu 1
Nama : Hamzah Wiranata\
Nim : 10241035\
Kelas : A

## 1.3 Read → Break → Fix → Build

### READ — Bedah instalasi Anda sendiri (45 menit)

1. Buka `public/index.php`. Baca dari atas ke bawah. Tulis dalam 3 kalimat apa yang dilakukan berkas ini.
2. Buka `bootstrap/app.php`. Identifikasi bagian mana yang mengurus route, mana yang mengurus middleware, mana yang mengurus exception.
3. Buka `routes/web.php`. Temukan route yang menghasilkan halaman selamat datang. Ubah teksnya, muat ulang browser, pastikan berubah.
4. Jalankan `php artisan route:list`. Cocokkan keluarannya dengan isi `routes/web.php`.

Jawaban

1. Menangani Request Pengguna
2. Hasil Idenetifikasi `bootstrap/app.php`
   - Yang mengurus route adalah 
        ```php
        ->withRouting(
            web: __DIR__.'/../routes/web.php',
            commands: __DIR__.'/../routes/console.php',
            health: '/up',
        )
        ```
   - Yang mengurus middleware adalah 
        ```php
        ->withMiddleware(function (Middleware $middleware) {
            //
        })
        ``` 
   - Yang mengurus exception adalah 
        ```php
        ->withExceptions(function (Exceptions $exceptions) {
            //
        })
        ``` 
3. Hasil temuan
   ```php
    Route::get('/', function () {
        return view('welcome');
    });
   ```
4. `php artisan route:list` dan `route/web.php`

    - `route/web.php`
        ```php
        Route::get('/', function () {
            return view('welcome');
        });
        ```
    - `php artisan route:list`
        ```shell
        PS C:\Users\Xufruz\OneDrive\Documents\Kuliah\Semester 5\Pemrograman Web\Github\lms-team-4> php artisan route:list

        GET|HEAD  / ................................................................................................................................................................................ routes/web.php:5
        GET|HEAD  storage/{path} ................................................................................ storage.local › vendor/laravel/framework/src/Illuminate/Filesystem/FilesystemServiceProvider.php:98
        PUT       storage/{path} ........................................................................ storage.local.upload › vendor/laravel/framework/src/Illuminate/Filesystem/FilesystemServiceProvider.php:106
        GET|HEAD  up .................................................................................................... vendor/laravel/framework/src/Illuminate/Foundation/Configuration/ApplicationBuilder.php:219

                                                                                                                                                                                                    Showing [4] routes
        ```
    Kecocokan keluaran dari `php artisan route:list` dengan `route/web.php` adalah `php artisan route:list` menampilkan daftar route yang ada di dalam `route/web.php` yang hanya ada route GET/ yang mengarah ke `welcome` view.
    - `GET|HEAD storage/{path}` & `PUT storage/{path}` adalah route untuk mengakses file di folder storage.
    - `GET|HEAD up` adalah route untuk mengakses halaman up.
### BREAK — Rusak dengan sengaja (30 menit)

Lakukan satu per satu, catat pesan errornya, lalu kembalikan:

| # | Yang dirusak | Prediksi Anda sebelum mencoba | Pesan error sebenarnya |
|---|--------------|-------------------------------|------------------------|
| 1 | Ganti nama `.env` menjadi `.env.bak` | Laravel tidak akan berjalan/eror karena saat sistem mencari `.env`, `.env` tidak ada | Tampilan awal menampilkan halaman eror dengan kalimat `This site can’t be reached` `127.0.0.1 refused to connect.`, serta di terminal menampilkan error `filemtime(): stat failed for C:\Users\Xufruz\OneDrive\Documents\Kuliah\Semester 5\Pemrograman Web\Github\lms-team-4\.env`|
| 2 | Kosongkan nilai `APP_KEY` di `.env` | Laravel akan tetap bisa berjalan, namun saat akan login akan eror | Tampilan awal Laravel menampilkan halaman error `No application encryption key has been specified.` |
| 3 | Ubah `DB_DATABASE` menjadi nama yang tidak ada | Tidak akan terjadi apa-apa karena sistem belum membutuhkan `DB_DATABASE` | Menampilkan tampilan error dan pesan `Database file at path [laravel] does not exist. Ensure this is an absolute path to the database. (Connection: sqlite, Database: laravel, SQL: select * from "sessions" where "id" = DwvQW9ReK4c9zObknEGJyxj0Z4KmSbYh6zDewdJ4 limit 1)` |
| 4 | Ubah `APP_DEBUG=false`, lalu ulangi nomor 3 | `APP_DEBUG=false` akan menyembunyikan tampilan error | Menampilkan `500 Server Error` |

### FIX — Perbaiki proyek yang cacat (30 menit)

Dosen menyediakan repo `kampuslms-broken`. Pindah ke branch `w01` — isinya proyek Laravel 12 yang tidak mau jalan. Ada **4 masalah**. Temukan dan perbaiki semuanya, lalu kirim Pull Request berisi penjelasan tiap perbaikan.

Petunjuk: masalahnya tersebar di berkas konfigurasi, dependensi, dan satu berkas yang seharusnya tidak ada di dalam repo.

### BUILD — Fondasi proyek kelompok (sisa waktu + tugas terstruktur)

- [x] 1. Buat repo kelompok di dalam Organization mata kuliah. Nama: `kampuslms-kelompok-XX`.
- [x] 2. Instal Laravel 12. Pastikan `php artisan serve` atau Herd berjalan.
- [x] 3. Buat `README.md` berisi: nama proyek, daftar anggota + NIM, cara instalasi, dan tabel pembagian peran.
- [x] 4. Pastikan `.env.example` lengkap dan `.env` **tidak** ter-commit. Verifikasi dengan `git status`.
- [x] 5. Setiap anggota membuat minimal satu commit atas nama dan email masing-masing.
- [x] 6. Aktifkan branch protection pada `main`.
- [x] 7. Buat satu route baru `/tentang` yang menampilkan view berisi nama kelompok dan anggotanya.
