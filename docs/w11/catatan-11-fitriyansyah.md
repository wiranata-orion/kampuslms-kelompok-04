## 11.3 Read  → Break → Fix → Build
**Target akhir minggu**:  Anda mengukur performa KampusLMS Anda sendiri, menemukan masalahnya, memperbaikinya, dan mendokumentasikan angka sebelum-sesudah.

### READ --Ukur aplikasi
ada di performa.md

### BREAK
| # | Yang dicoba | Yang dipelajari |
|---|-------------|------------------------|
| 1 | Pasang `Model::preventLazyLoading()`, jalankan seluruh halaman | banyak |
| 2 | Hapus semua `with()` dari satu controller, ukur ulang | - |
| 3 | Ganti `withCount('students')` menjadi `count($course->students)`, ukur memori | memory yang terpakai count lebih banyak ketimbang withCount |
| 4 | Hapus index dari `assignments.course_id`, jalankan `EXPLAIN` | `type` berubah menjadi `ALL` |
| 5 | Naikkan seeder menjadi 300 mahasiswa dan 1000 submission, buka rekap nilai | query jadi bengkak, website jadi lemot |
| 6 | Jalankan `config:cache`, lalu panggil `env('APP_NAME')` dari sebuah controller | **Mengembalikan null** — ingat baik-baik |
| 7 | Pasang cache 10 menit pada nilai mahasiswa, ubah nilainya, muat ulang | Data basi yang merugikan pengguna. Data yang seharusnya terupdate dan harus dilihat pengguna dengan cepat, malah jadi lama karena tertumpuk cache |

### Build

file yang kubutuhkan sebelum implementasi:
- File Rekap Nilai: controller, resource, route, dan halaman Vue-nya.
- `PortalController.php` dan `AppServiceProvider.php` versi terbaru setelah build Austin.
- Isi baris CACHE_STORE dari .env.
- `config/cache.php`, migrasi `create_cache_table.php`

