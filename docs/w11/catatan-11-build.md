# BUILD — Optimasi Query dan Database, Cache, Config, dan Dokumentasi

new file:
1. database/migration/2026_10_31_092456_create_telescope_entries_table
2. docs/performa.md
3. telescope.php
4. TelescopeServiceProvide.php
5. config/cache.php
6. tests/Feature/ApiContractTest.php
7. database/migrations/2026_10_31_110451_add_performance_indexes_to_grades_table.php
8. tests/Feature/DashboardCacheTest.php
9. tests/Unit/NoEnvOutsideConfigTest.php
10. docs/w11/catatan-11-build.md
11. app/support/DashboardCache.php
12. docs/w11/cache-config-performa.md
13. docs/w11/optimasi-database.md
14. docs/w11/optimasi-query.md

update file:
1. app/Http/Controllers/Api/SubmissionController.php
2. resources/js/app.vue
3. app/Providers/AppServiceProvider.php
4. app/Http/Controllers/Api/SubmissionController.php
5. app/Policies/SubmissionPolicy.php
6. app/Http/Controllers/Api/DashboardController.php
7. app/Http/Controllers/Api/CourseEnrollmentController.php	
8. database/seeders/DemoAccountSeeder.php

 
### Hasil pemeriksaan implementasi checklist:
terpenuhi = **100%**, sebagian = **50%**, belum = **0%**
Kondisi kode sebelum optimasi: **42%**

| Kriteria | Status | Temuan |
|----------|--------|--------|
| `Model::preventLazyLoading()` | Sebagian | `AppServiceProvider.php` memakai `Model::shouldBeStrict()` hanya di luar production. Jadi lazy-loading dicegah saat development/test, tetapi tidak saat production, dan pemanggilan `preventLazyLoading()` tidak dinyatakan langsung. |
| Memperbaiki seluruh N+1 | Sebagian | Banyak daftar sudah memakai eager loading, misalnya rekap nilai di `GradeSummaryController.php.` Namun, `SubmissionResource.php` memeriksa izin update untuk setiap submission. Untuk mahasiswa, `SubmissionPolicy.php` menjalankan pemeriksaan enrollment dan grade per submission—potensi N+1 yang masih tersisa. |
| Rekap nilai kurang dari 15 query | Terindikasi terpenuhi | Kedua query rekap memuat relasi yang dipakai resource dan memakai pagination: `GradeSummaryController.php`. Secara statis, query rekap diperkirakan sekitar 6 query untuk hitungan, data, dan eager loading relasi; tetap di bawah 15. |
| Migration tambahan berisi index dan komentar query yang diuntungkan | Belum | `database/migrations/` tidak menunjukkan migration tambahan khusus index dengan komentar query yang diuntungkan. Ada index di migration lain, misalnya `create_assignments_table.php`, tetapi itu belum memenuhi kriteria tersebut. | 
| Semua halaman daftar memakai pagination dan tidak mengambil seluruh tabel | Sebagian | Daftar utama menggunakan pagination. Namun, endpoint pilihan dosen di `UserController.php` dan kandidat mahasiswa di `CourseEnrollmentController.php` memakai `get()` tanpa pagination. Pada detail mata kuliah, daftar tugas/materi juga tidak menyediakan navigasi pagination di UI. |
| Cache beserta pemberishan otomatis ketika data berubah | Belum | Konfigurasi cache laravel tersedia di `config/cache.php`, tetapi tidak ada penggunaan cache aplikasi. Observer yang ada mengurus penghapusan berkas, bukan invalidasi cache. Infrastruktur sudah tersedia, tetapi pengaplikasiannya belum |

