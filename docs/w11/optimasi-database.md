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