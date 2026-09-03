# Catatan Minggu 1
Nama : Jeshua Austin Daceka\
Nim : 10241037\
Kelas : A

## 1.3 Read → Break → Fix → Build

### READ — Bedah instalasi Anda sendiri (45 menit)

1. Buka `public/index.php`. Baca dari atas ke bawah. Tulis dalam 3 kalimat apa yang dilakukan berkas ini.
2. Buka `bootstrap/app.php`. Identifikasi bagian mana yang mengurus route, mana yang mengurus middleware, mana yang mengurus exception.
3. Buka `routes/web.php`. Temukan route yang menghasilkan halaman selamat datang. Ubah teksnya, muat ulang browser, pastikan berubah.
4. Jalankan `php artisan route:list`. Cocokkan keluarannya dengan isi `routes/web.php`.

Jawaban

1. `public/index.php` adalah file awal yang menerima request dari pengguna ke aplikasi Laravel. File ini mengecek mode maintenance, memuat Composer, lalu menjalankan konfigurasi aplikasi Laravel. Setelah itu, request diteruskan ke Laravel untuk diproses dan menghasilkan response.

2. Hasil identifikasi `bootstrap/app.php`:
   - Bagian yang mengurus route adalah `withRouting(...)`, isinya menentukan file route untuk web dan command serta endpoint health check.
   - Bagian middleware adalah `withMiddleware(...)`, masih kosong karena belum ada middleware tambahan yang perlu dikonfigurasi.
   - Bagian exception adalah `withExceptions(...)`, juga masih kosong karena belum ada penanganan exception khusus yang dikonfigurasi.

3. Route welcome ditemukan di `routes/web.php`:
   ```php
   Route::get('/', function () {
       return view('welcome');
   });
   ```
   Setelah teks diubah menjadi `return 'halo dari kelompok 4';` dan browser di-reload, tampilan berubah sesuai teks baru, membuktikan route ini yang dijalankan setiap ada request ke `/`.

4. Hasil `php artisan route:list`:
   ```
   GET|HEAD  /                 routes/web.php:5
   GET|HEAD  storage/{path}    storage.local
   PUT       storage/{path}    storage.local.upload
   GET|HEAD  up                (health check bawaan)
                                Showing [4] routes
   ```
   Hasil ini cocok dengan `routes/web.php`, ada route `GET|HEAD /` sesuai baris 5, ditambah 3 route bawaan Laravel: `storage/{path}` untuk akses file di folder storage, dan `up` untuk memastikan server hidup.

### BREAK — Rusak dengan sengaja (30 menit)

Lakukan satu per satu, catat pesan errornya, lalu kembalikan:

| # | Yang dirusak | Prediksi Anda sebelum mencoba | Pesan error sebenarnya |
|---|--------------|-------------------------------|------------------------|
| 1 | Ganti nama `.env` menjadi `.env.bak` | Halaman error, tapi tidak tahu errornya soal apa | Muncul `500 Server Error` polos tanpa detail. Server sempat tidak bisa diakses (`ERR_CONNECTION_REFUSED`) karena proses server berhenti, lalu setelah dijalankan ulang muncul `500` tanpa keterangan, karena Laravel tidak bisa membaca `.env`, termasuk `APP_DEBUG` yang menentukan apakah detail error ditampilkan atau tidak |
| 2 | Kosongkan nilai `APP_KEY` di `.env` | Error spesifik yang menyebut soal "encryption key" | `Illuminate\Encryption\MissingAppKeyException`: "No application encryption key has been specified." Detail error muncul lengkap (nama exception, file, baris) karena `.env` masih terbaca normal, hanya `APP_KEY`-nya yang kosong, beda dengan percobaan 1 di mana seluruh `.env` tidak terbaca |
| 3 | Ubah `DB_DATABASE` menjadi nama yang tidak ada | Halaman awal (`/`) tetap normal karena belum mengakses database | Halaman awal tetap normal adalah benar. Tapi menjalankan `php artisan migrate` di terminal membuat Laravel malah menawarkan membuat file SQLite baru bernama sesuai `DB_DATABASE` yang diganti (bukan langsung error), karena project ini masih menggunakan `DB_CONNECTION=sqlite`, bukan MySQL sesuai spec proyek |
| 4 | Ubah `APP_DEBUG=false`, lalu ulangi nomor 3 | Error jadi lebih singkat/polos, tanpa detail teknis | Benar. `APP_DEBUG` hanya memengaruhi tampilan error di **browser**, bukan command di terminal, jadi percobaan lewat `php artisan migrate` hasilnya sama saja. Untuk melihat efeknya, dibuat route sementara `/tes-db` yang memaksa koneksi ke database lewat browser: hasilnya cuma `500 Server Error` polos tanpa nama exception atau lokasi file, berbeda dari percobaan 2 (`APP_DEBUG=true`) yang menampilkan detail lengkap. Ini menunjukkan kenapa `APP_DEBUG=false` wajib di production, mencegah detail struktur kode/database bocor ke pengguna |

### FIX — Perbaiki proyek yang cacat (30 menit)

### BUILD — Fondasi proyek kelompok (sisa waktu + tugas terstruktur)
