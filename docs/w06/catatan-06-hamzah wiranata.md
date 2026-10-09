### READ

1. Jalankan `php artisan install:api`. Baca perubahan yang terjadi di `bootstrap/app.php`.
2. Buat satu endpoint `GET /api/v1/courses` sederhana.
3. Bandingkan dengan `CourseController` versi web yang sudah ada. Tulis di catatan: apa yang **sama** dan apa yang **berbeda** di antara keduanya?
4. Panggil endpoint API tanpa header `Accept: application/json`. Lalu dengan header itu. Catat bedanya.
5. Jalankan `php artisan route:list --path=api`. Cocokkan dengan kontrak di spesifikasi.

### Jawaban

1. Perubahan pada `bootstrap/app.php`
      - Sebelum
        ```php
        return Application::configure(basePath: dirname(__DIR__))
            ->withRouting(
                web: __DIR__.'/../routes/web.php',
                commands: __DIR__.'/../routes/console.php',
                health: '/up',
            )
            ->withMiddleware(function (Middleware $middleware) {
                //
            })
            ->withExceptions(function (Exceptions $exceptions) {
                //
            })->create();
        ``` 
    - Sesudah
        ```php
        return Application::configure(basePath: dirname(__DIR__))
            ->withRouting(
                web: __DIR__.'/../routes/web.php',
                api: __DIR__.'/../routes/api.php', // <-- Baru ditambahkan
                commands: __DIR__.'/../routes/console.php',
                health: '/up',
            )
            ->withMiddleware(function (Middleware $middleware) {
                //
            })
            ->withExceptions(function (Exceptions $exceptions) {
                //
            })->create();
        ```

2. Endpoint `GET /api/v1/courses`

   Endpoint sudah didaftarkan di `routes/api.php` pada grup prefix `v1` dan middleware `auth:sanctum`. Route ini memanggil `CourseApiController@index`. Jika pengguna sudah terautentikasi, endpoint mengembalikan daftar course dalam JSON: admin melihat semua course, dosen melihat course yang diampu, dan mahasiswa melihat course yang diikutinya. Respons dipaginasi 15 data per halaman dan menyertakan informasi dosen serta jumlah materi dan tugas. Permintaan tanpa autentikasi ditolak dengan `401`.

3. Perbandingan dengan `CourseController` versi web

   **Sama:**
   - Keduanya menyediakan daftar mata kuliah yang diurutkan berdasarkan nama.
   - Keduanya memuat relasi dosen pengampu dan menggunakan pagination 15 data per halaman.
   - Keduanya hanya dapat digunakan setelah pengguna login.

   **Berbeda:**
   - `CourseController@index` menampilkan halaman Blade `courses.index`; `CourseApiController@index` mengembalikan JSON dengan resource dan metadata pagination.
   - Versi web menampilkan katalog untuk pengguna yang login dan mendukung filter `search` dan `status`. Versi API membatasi data berdasarkan peran: admin melihat semua, dosen hanya course yang diampu, dan mahasiswa hanya course yang diikuti.
   - Versi API menyertakan jumlah materi dan tugas; versi web memuat data dosen untuk tampilan.
   - Dengan demikian, endpoint API bukan sekadar versi JSON dari daftar web: aturan cakupan data keduanya berbeda.

4. Perbandingan permintaan tanpa dan dengan `Accept: application/json`

   Endpoint diuji pada `http://127.0.0.1:8000/api/v1/courses` tanpa token autentikasi:

   ```bash
   curl -i http://127.0.0.1:8000/api/v1/courses
   curl -i -H "Accept: application/json" http://127.0.0.1:8000/api/v1/courses
   ```

   Hasil kedua permintaan sama: HTTP `401 Unauthorized`, `Content-Type: application/json`, dan body `{"message":"Unauthenticated."}`. Penyebabnya, endpoint dilindungi `auth:sanctum`; menambahkan `Accept: application/json` tidak mengubah respons untuk permintaan tanpa autentikasi.

5. Hasil `php artisan route:list --path=api` dan pencocokan spesifikasi

    Lima belas route API utama cocok dengan endpoint yang didokumentasikan pada `docs/api.md`:

   | Method | URI |
   |---|---|
   | `POST` | `/api/v1/auth/login` |
   | `POST` | `/api/v1/auth/logout` |
   | `GET` | `/api/v1/me` |
   | `GET` | `/api/v1/courses` |
   | `GET` | `/api/v1/courses/{course}` |
   | `GET` | `/api/v1/courses/{course}/materials` |
   | `GET` | `/api/v1/courses/{course}/assignments` |
   | `POST` | `/api/v1/assignments` |
   | `PUT`, `PATCH` | `/api/v1/assignments/{assignment}` |
   | `DELETE` | `/api/v1/assignments/{assignment}` |
   | `GET` | `/api/v1/assignments/{assignment}/submissions` |
   | `POST` | `/api/v1/assignments/{assignment}/submissions` |
   | `PUT` | `/api/v1/submissions/{submission}/grade` |
   | `GET` | `/api/v1/notifications` |
   | `POST` | `/api/v1/notifications/{notification}/read` |

    Semua route di atas tampil pada hasil perintah; endpoint selain login berada di dalam middleware `auth:sanctum`.

   Verifikasi kontrak API: seluruh 9 pengujian pada `ApiContractTest` berhasil.