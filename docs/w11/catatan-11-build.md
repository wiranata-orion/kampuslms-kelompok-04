# BUILD — Optimasi Query dan Database KampusLMS

## 1. Ringkasan Pekerjaan
Fokus pekerjaan meliputi pencegahan lazy loading, pengurangan query N+1, optimasi rekap nilai, penambahan index database, dan pagination halaman daftar.
---
## 2. Branch `feat/optimasi-query`
### A. Pencegahan Lazy Loading
**Status: Selesai**
Pemeriksaan lazy loading diterapkan melalui `app/Providers/AppServiceProvider.php`:
```php
Model::shouldBeStrict(! $this->app->isProduction());
```
Strict mode aktif di luar production untuk membantu mendeteksi akses relasi yang belum dimuat. Pengujian manual pada role Admin, Dosen, dan Mahasiswa tidak menemukan `LazyLoadingViolationException` pada alur yang diperiksa.
### B. Optimasi Query Rekap Nilai
**Status: Dilaporkan selesai**
Jumlah query turun dari **15 menjadi 8 query**, berkurang 7 query atau sekitar 46,7%.
| Metrik | Sebelum optimasi |
|---|---:|
| Total query | 15 |
| Query duplikat | 3 |
| Query unik | 12 |
| Query duration | 47,13 ms |
| Memory | 4 MB |
| Total request | 151 ms |
Detail perubahan kode dan metrik setelah optimasi selain jumlah query belum terkonfirmasi.
### C. Optimasi N+1 pada Pengumpulan Saya
**Status: Implementasi dan pengujian selesai**
Endpoint yang diperiksa: `GET /api/v1/my/submissions?page=1`.
**Temuan awal:**
- Query berulang saat memeriksa nilai melalui relasi `grades`.
- Query berulang saat memeriksa keanggotaan mahasiswa pada course melalui pivot `course_user`.
- Penyebabnya adalah pemeriksaan permission pada resource dan policy untuk setiap submission.
**Perubahan implementasi:**
1. `app/Http/Controllers/Api/SubmissionController.php`
   - Method `mine()` menggunakan eager loading `assignment.course.students` dan `grade`.
   - Relasi mahasiswa dibatasi berdasarkan ID mahasiswa yang login.
   - Pagination backend tetap menggunakan `->paginate(15)`.
2. `app/Policies/SubmissionPolicy.php`
   - Method `update()` memanfaatkan relasi yang sudah dimuat.
   - Pengecekan keanggotaan course menggunakan collection jika relasi `students` tersedia; jika belum, digunakan fallback query.
   - Pengecekan nilai memanfaatkan relasi `grade`, menghindari query `exists()` berulang.
3. `tests/Feature/ApiContractTest.php`
   - Menguji kepemilikan dan pembatasan data submission.
   - Menguji permission submission yang sudah memiliki nilai.
   - Memeriksa jumlah query `grades` dan `course_user`.
   - Menguji fallback policy ketika relasi belum dimuat.
**Hasil pengujian:**
```powershell
php artisan test --filter=my_submissions_are_scoped_and_permissions_use_batched_relations
```
Hasil: 11 assertions, 1 deprecated, tanpa assertion failure.
```powershell
php artisan test --filter=ApiContractTest
```
Hasil: 186 assertions, 19 deprecated, tanpa assertion failure.
Status deprecated bukan berarti pengujian gagal. Pada skenario pengujian, query `grades` dan `course_user` masing-masing hanya dijalankan satu kali, bukan berulang untuk setiap submission. Benchmark waktu respons setelah optimasi belum tercatat.
---
## 3. Branch `feat/optimasi-database`
### A. Penambahan Index Database
**Status: Selesai**
Migration: `database/migrations/2026_10_31_110451_add_performance_indexes_to_grades_table.php`.
Index ditambahkan atau diverifikasi pada:
- `grades.graded_at`
- `grades.published_at`
Index yang sudah ditemukan pada database:
| Tabel | Kolom |
|---|---|
| `users` | `role`, `email`, `nim_nip` |
| `materials` | `course_id` |
| `assignments` | `course_id`, `due_at` |
| `submissions` | `assignment_id`, `user_id` |
| `grades` | `submission_id` |
| `logs` | `submission_id`, `created_at` |
Index yang sudah tersedia perlu diperhitungkan agar tidak terjadi duplikasi atau penambahan index tanpa kebutuhan query yang jelas.
### B. Pagination Halaman Daftar
**Status: Belum selesai**
Pagination sempat dicoba, tetapi implementasi percobaan sebelumnya dibersihkan dan belum dikerjakan ulang.
Ketentuan implementasi:
- Diterapkan pada seluruh halaman daftar target, bukan hanya Admin → Pengguna.
- Setiap halaman menampilkan 20 data; halaman berikutnya berisi 20 data selanjutnya tanpa pengulangan.
- Navigasi pagination selalu tampil, termasuk ketika jumlah data kurang dari 10.
- Tombol Selanjutnya aktif jika halaman berikutnya tersedia dan nonaktif jika tidak tersedia.
- Admin → Pengguna menjadi acuan tampilan dan perilaku.
Daftar halaman target, file implementasi, dan hasil pengujian belum dapat dinyatakan selesai sebelum implementasi ulang dan verifikasi.
---
## 4. Ringkasan Status Deliverable
| Pekerjaan | Branch | Status |
|---|---|---|
| Strict mode lazy loading | `feat/optimasi-query` | Selesai |
| Optimasi rekap nilai (15 menjadi 8 query) | `feat/optimasi-query` | Dilaporkan selesai |
| Optimasi N+1 Pengumpulan Saya | `feat/optimasi-query` | Selesai dan diuji |
| Index `graded_at` dan `published_at` | `feat/optimasi-database` | Selesai |
| Pagination seluruh halaman target | `feat/optimasi-database` | Belum selesai |
---
## 5. File yang Perubahannya Teridentifikasi
**Branch `feat/optimasi-query`:**
- `app/Providers/AppServiceProvider.php` — konfigurasi strict mode.
- `app/Http/Controllers/Api/SubmissionController.php` — eager loading endpoint Pengumpulan Saya.
- `app/Policies/SubmissionPolicy.php` — pemanfaatan relasi untuk mengurangi query.
- `tests/Feature/ApiContractTest.php` — pengujian query dan permission.
File perubahan optimasi rekap nilai belum dapat dipastikan.
**Branch `feat/optimasi-database`:**
- `database/migrations/2026_10_31_110451_add_performance_indexes_to_grades_table.php` — penambahan atau verifikasi index tabel `grades`.
File implementasi pagination belum dapat dicantumkan karena pengerjaannya belum selesai.
---

## 6. Hasil Pengujian dan Catatan Git
| Pengujian | Hasil |
|---|---|
| Strict mode pada Admin, Dosen, Mahasiswa | Tidak ditemukan `LazyLoadingViolationException` pada alur yang diperiksa |
| Endpoint Pengumpulan Saya | 11 assertions, tanpa assertion failure |
| API Contract | 186 assertions, tanpa assertion failure |
| Query `grades` | 1 query pada skenario pengujian |
| Query `course_user` | 1 query pada skenario pengujian |
| Optimasi rekap nilai | Query turun dari 15 menjadi 8 |
Benchmark waktu respons setelah optimasi dan hasil build akhir belum terkonfirmasi.
Catatan Git:
- `feat/optimasi-query` sebelumnya tercatat tracking `origin/feat/optimasi-query`.
- `feat/optimasi-database` pernah di-reset agar sesuai dengan `origin/feat/optimasi-database`.
- Migration index dipertahankan sebagai file untracked setelah reset branch database.
- `storage/debugbar/` tercatat untracked dan bukan bagian perubahan fitur yang perlu di-commit.
- Status commit dan push terakhir seluruh perubahan belum terkonfirmasi.