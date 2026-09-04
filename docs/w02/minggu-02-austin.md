# Catatan Minggu 2
Nama : Jeshua Austin Daceka
Nim : 10241037
Kelas : A

## 2.1 Konsep (Ringkasan)

- **Route** adalah tabel pencocokan, Laravel membaca `routes/web.php` dari atas ke bawah dan memakai yang pertama cocok.
- Method HTTP itu bermakna, GET untuk mengambil data, POST untuk membuat, PUT/PATCH untuk mengubah, DELETE untuk menghapus.
- Nama route (`->name(...)`) dipakai lewat `route('nama.route', ...)` di view, jangan tulis URL langsung, supaya kalau URL berubah cukup edit satu baris di `web.php`.
- Urutan route menentukan, route yang lebih spesifik harus ditaruh di atas route yang pakai parameter/wildcard.
- Controller yang sehat itu pendek, tugasnya menerima request, meminta data, menyerahkan ke view.
- Blade `{{ }}` aman karena HTML di-escape otomatis, sedangkan `{!! !!}` bahaya karena HTML dieksekusi mentah, ini jadi pertahanan utama terhadap XSS.
- Layout dipakai lewat komponen Blade (`<x-layout>`), bukan disalin-tempel ke tiap halaman.
- `@vite(...)` menghubungkan Blade ke Vite, saat development jalankan `npm run dev`, saat deploy jalankan `npm run build`.

## 2.3 READ - Telusuri satu request penuh

Route yang ditelusuri: `/tentang`

1. Baris yang menangkap `/tentang` ada di `routes/web.php`:
   ```php
   Route::get('/tentang', function () {
       return view('tentang');
   });
   ```

2. Controller dan method: tidak ada, route ini pakai closure langsung di `web.php`, tidak lewat controller terpisah.

3. View yang dikembalikan: `tentang.blade.php`, berada di `resources/views/tentang.blade.php`.

4. Layout yang membungkus: awalnya tidak ada (masih HTML polos), setelah dirapikan sekarang dibungkus `<x-layout>` yang ada di `resources/views/components/layout.blade.php`.

5. Hasil `php artisan route:list --path=tentang`:
   ```
   GET|HEAD  tentang    routes/web.php:8
   Showing [1] routes
   ```
   Hasil ini cocok dengan analisis di atas, hanya ada satu route `GET|HEAD` untuk `tentang`.

## 2.3 BREAK - Delapan kerusakan

| # | Yang dirusak | Prediksi | Yang terjadi sebenarnya |
|---|---|---|---|
| 1 | Ubah `Route::get` menjadi `Route::post` pada route `/courses` | Error karena browser otomatis kirim request GET, sedangkan route sekarang minta POST | `405 Method Not Allowed`, `MethodNotAllowedHttpException`, pesan "The GET method is not supported for route courses. Supported methods: POST." |
| 2 | Ubah nama view di `return view(...)` menjadi yang tidak ada (`courses.indexx`) | Error karena Laravel mencari file yang tidak ada | `InvalidArgumentException`, "View [courses.indexx] not found" |
| 3 | Hapus `->name('courses.show')`, lalu muat halaman yang memakai `route('courses.show')` | Halaman `/courses` langsung error duluan, karena `route()` dipanggil saat render, bukan saat link diklik | `RouteNotFoundException`, "Route [courses.show] not defined", muncul saat membuka `/courses` |
| 4 | Pindahkan `/courses/{course}` ke atas `/courses/create`, lalu buka `/courses/create` | Ketangkap duluan oleh `{course}`, karena "create" dianggap sebagai nilai `$course` | Terbukti benar, mengakses `/courses/create` malah memicu `404 Not Found` dari `abort(404)` di controller karena Laravel mencari data dengan id "create". Setelah `/courses/create` dipindah ke atas `{course}`, route itu baru berhasil ditangkap dan menampilkan "Form buat mata kuliah baru" |
| 5 | Ganti `{{ $nama }}` menjadi `{!! $nama !!}`, isi `$nama` dengan `<script>alert("XSS")</script>` | Dengan `{{ }}` teks tampil apa adanya, dengan `{!! !!}` script benar-benar dijalankan browser | Terbukti, dengan `{{ }}` teks `<script>alert("XSS")</script>` muncul sebagai teks polos di halaman. Setelah diganti `{!! !!}`, popup alert JavaScript benar-benar muncul di browser |
| 6 | Hapus `@vite(...)` dari layout | Halaman tetap muncul tapi tanpa styling karena CSS/JS tidak ke-load | Halaman tetap terbuka normal tanpa error, karena project belum memakai styling custom efeknya belum kelihatan, tapi CSS/JS dari `resources/css/app.css` dan `resources/js/app.js` memang tidak lagi dimuat |
| 7 | Hentikan `npm run dev`, lalu muat ulang halaman | CSS/JS tidak ke-load atau error karena `@vite` terhubung ke dev server yang sedang jalan | Ternyata bukan cuma kehilangan styling, tapi halaman error total, `ViteManifestNotFoundException`, "Vite manifest not found". Ini beda dengan mode `npm run build` untuk production yang membuat file manifest permanen tanpa perlu server jalan terus |
| 8 | Panggil `route('courses.show')` tanpa mengirim parameter | Error karena Laravel butuh tahu nilai parameter `{course}` untuk membuat URL | `UrlGenerationException`, "Missing required parameter for [Route: courses.show] [URI: courses/{course}] [Missing parameter: course]" |

### FIX - Repo cacat

### BUILD - Kerangka KampusLMS

- [x] Layout `x-layout` dengan navbar berisi Dashboard, Mata Kuliah, Tentang (`resources/views/components/layout.blade.php`)
- [x] `CourseController` dengan method `index` dan `show`, masih memakai data statis (array)
- [x] `courses/index.blade.php`, daftar mata kuliah, tautan memakai `route()`
- [x] `courses/show.blade.php`, detail satu mata kuliah
- [ ] Halaman 404 kustom (`resources/views/errors/404.blade.php`), belum dibuat
- [x] Commit dari lebih dari satu anggota, sudah ada commit dari Jeshua dan anggota lain di repo