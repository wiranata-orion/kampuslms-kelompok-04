# Catatan Minggu 6

Nama : Jeshua Austin Daceka  
NIM : 10241037  
Kelas : A

### READ

**1. Perubahan pada `bootstrap/app.php`**

Setelah menjalankan `php artisan install:api`, konfigurasi routing API ditambahkan agar Laravel dapat memuat route dari `routes/api.php`.

**2. Endpoint `GET /api/v1/courses`**

Endpoint berada di `routes/api.php` dan menggunakan middleware `auth:sanctum`. Data dikirim dalam format JSON dengan pagination 15 data. Admin dapat melihat semua mata kuliah, dosen melihat mata kuliah yang diampu, sedangkan mahasiswa melihat mata kuliah yang diikuti. Pengguna tanpa autentikasi mendapat respons `401 Unauthorized`.

**3. Perbandingan dengan `CourseController`**

**Persamaan:**
- Menampilkan daftar mata kuliah berdasarkan nama.
- Menggunakan pagination 15 data dan memuat relasi dosen.
- Sama-sama memerlukan login.

**Perbedaan:**
- `CourseController` menampilkan halaman Blade, sedangkan `CourseApiController` mengirim respons JSON.
- Versi web mendukung pencarian dan filter status, sedangkan API membatasi data berdasarkan role pengguna.
- API menyertakan jumlah materi dan tugas.

**4. Pengujian header `Accept: application/json`**

Kedua permintaan, baik tanpa maupun dengan header `Accept: application/json`, menghasilkan `401 Unauthorized` dengan pesan `Unauthenticated.` karena endpoint membutuhkan autentikasi Sanctum.

**5. Pemeriksaan route API**

Hasil `php artisan route:list --path=api` menunjukkan 15 route utama yang sesuai dengan daftar endpoint pada `docs/api.md`. Route selain login dilindungi middleware `auth:sanctum`. Seluruh 9 pengujian pada `ApiContractTest` juga berhasil.