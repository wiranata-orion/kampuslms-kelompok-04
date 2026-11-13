## 9.3 Read  → Break → Fix → Build
**Target akhir minggu**:  Dosen bisa mengunggah materi, mahasiswa bisa mengumpulkan tugas, dan tidak seorang pun bisa mengunduh berkas milik orang lain.

### READ

Unggah satu berkas materi:

1. Temukan berkas fisiknya di sistem berkas. Path lengkapnya apa?
2. Buka baris database yang bersangkutan. Bandingkan `file_path` dengan lokasi fisiknya.
3. Coba akses berkas itu langsung lewat browser dengan menebak URL-nya. Berhasil atau tidak? Kenapa?
4. Buka `config/filesystems.php.` Identifikasi disk `local` dan `public` — apa persis bedanya?
5. Jalankan `php artisan storage:link`. Apa yang dibuatnya? Kenapa materi Anda tidak boleh ada di sana?

#### Jawaban

1. Saya menelusuri berkas di `storage/app/private/materials`. Salah satu berkas fisik yang ada adalah:

   ```text
   C:\Users\Xufruz\OneDrive\Documents\Kuliah\Semester 5\Pemrograman Web\kelompok-lms\storage\app\private\materials\1\1kvz8oc7O3KNbKWpZImup3QtLBPXxMduWymFpXGG.pdf
   ```

   Pengecekan `Test-Path` untuk berkas tersebut menghasilkan `True`. Ini adalah path absolut di komputer pengembangan ini.

2. Saya membaca tabel `materials` melalui model Laravel. Contoh baris `id = 1` memiliki `file_path`:

   ```text
   materials/0f7deeab-30e0-3fa7-912d-cd050b07317a.pdf
   ```

   Karena disk `local` ber-root di `storage/app/private`, Laravel akan mencari berkas itu di:

   ```text
   C:\Users\Xufruz\OneDrive\Documents\Kuliah\Semester 5\Pemrograman Web\kelompok-lms\storage\app\private\materials\0f7deeab-30e0-3fa7-912d-cd050b07317a.pdf
   ```

   Berkas pada path hasil tersebut **tidak ditemukan** (`Storage::disk('local')->exists(...)` menghasilkan `false`). Berkas fisik yang saya temukan berada di subfolder `materials/1/` dan bernama hash. Query database juga tidak menemukan baris yang merujuk ke contoh berkas fisik tadi. Jadi database dan berkas lokal di checkout ini tidak cocok; jangan menganggap `file_path` pada baris contoh menunjuk ke berkas fisik yang ada.

3. URL yang mungkin ditebak dari `file_path` baris tadi adalah:

   ```text
   http://127.0.0.1:8000/storage/materials/0f7deeab-30e0-3fa7-912d-cd050b07317a.pdf
   ```

   URL itu tidak dapat menemukan file: pemeriksaan `public/storage/materials/...` menghasilkan `False`, dan isi `file_path` tersebut juga tidak ada di disk `local`. Selain itu, `/storage` hanya menunjuk ke disk `public`, bukan ke `storage/app/private`. Untuk materi yang benar, aplikasi memakai endpoint `GET /api/v1/materials/{material}/download`; controller menjalankan policy sebelum mengirim file. Jadi file privat memang tidak seharusnya bisa dibuka dengan menebak URL statis.

4. Di `config/filesystems.php`:

   | Disk | Root | URL / visibility | Dampak |
   | --- | --- | --- | --- |
   | `local` | `storage/app/private` | Tidak punya URL publik atau `visibility: public`; `serve` bernilai `true` | Untuk file privat. Proyek menyimpan materi dan submission di sini lalu mengirimnya melalui controller setelah otorisasi. |
   | `public` | `storage/app/public` | URL `APP_URL/storage`, `visibility: public` | Untuk file yang memang boleh diakses publik melalui link `public/storage`. |

   Nilai `default` di konfigurasi diambil dari `FILESYSTEM_DISK` dan fallback-nya `local`. Kode upload materi secara eksplisit menyimpan file dengan disk `local`.

5. Saya menjalankan `php artisan storage:link`. Laravel mengonfigurasi link yang seharusnya dibuat sebagai:

   ```text
   public/storage -> storage/app/public
   ```

   Namun `public/storage` sudah ada sebagai Windows Junction. Perintah menampilkan pesan OS `Cannot create a file when that file already exists` lalu pesan informasi bahwa link telah dihubungkan. Pemeriksaan sesudahnya menunjukkan junction yang ada masih menunjuk ke folder `Github\storage\app\public`, bukan `kelompok-lms\storage\app\public`. Jadi pada checkout ini perintah tersebut tidak membuat link baru ke target yang benar.

   Materi dan submission tidak boleh ditaruh di `storage/app/public`: link itu membuat isi disk `public` bisa diakses lewat web, sehingga akses dapat melewati autentikasi dan policy aplikasi. Di proyek ini `PrivateFiles` menetapkan disk `local`, dan controller materi memanggil policy sebelum mengirim file. Simpan file privat di `storage/app/private` dan sajikan lewat endpoint terproteksi.
### BREAK

| # | Yang dirusak | Yang dipelajari |
|---|--------------|-----------------|
| 1 | Pindahkan berkas submission ke `storage/app/public`, akses URL-nya tanpa login |  |
| 2 | Hapus `Gate::authorize()` dari endpoint download, akses submission orang lain |  |
| 3 |  Simpan berkas dengan `storeAs()` memakai nama asli, lalu dua akun mahasiswa berbeda mengunggah `tugas.pdf` dengan isi berbeda ke tugas yang sama |  |
| 4 | Unggah berkas PHP yang diganti namanya jadi `.pdf` dengan validasi `extensions`, lalu dengan `mimes` |  |
| 5 | Ubah `max:5120` menjadi `max:5`, unggah berkas 1 MB |  |
| 6 | Set `upload_max_filesize=1M` di php.ini, unggah berkas 3 MB dengan validasi `max:5120` |  |
| 7 | Hapus `updateOrCreate`, ganti `create`, kumpulkan tugas dua kali | |
| 8 | Hapus satu submission dari database lewat SQL langsung, cek folder storage | |

### Build


## 1. Daftar file
 
| Status | File | Isi perubahan |
|---|---|---|
| **NEW** | `app/Support/PrivateFiles.php` | Pintu tunggal berkas privat: kirim stream (unduh/pratinjau), hapus berkas setelah commit |
| **NEW** | `app/Observers/MaterialObserver.php` | Hapus berkas saat materi dihapus, diganti, atau diubah menjadi link |
| **NEW** | `app/Observers/SubmissionObserver.php` | Hapus berkas saat submission dihapus atau ditimpa (kumpul ulang) |
| **NEW** | `app/Observers/AssignmentObserver.php` | Catat path berkas submission sebelum `cascadeOnDelete`, hapus setelah commit |
| **NEW** | `app/Observers/CourseObserver.php` | Sama, untuk berkas materi dan submission milik mata kuliah |
| **NEW** | `app/Models/SubmissionAccessLog.php` | Model log audit akses berkas submission |
| **NEW** | `database/migrations/2026_10_16_000001_create_submission_access_logs_table.php` | Tabel `submission_access_logs` |
| **UPDATE** | `app/Http/Controllers/Api/PortalController.php` | `downloadMaterial` ke disk privat + `Gate::authorize('download')`; hapus penghapusan manual di disk `public` |
| **UPDATE** | `app/Http/Controllers/Api/SubmissionController.php` | `download` memakai `Gate::authorize('download')`, mendukung `?preview=1`, mencatat audit |
| **UPDATE** | `app/Policies/MaterialPolicy.php` | Tambah method `download()` |
| **UPDATE (manual)** | `app/Policies/SubmissionPolicy.php` | Tambah method `download()`. Versimu sudah kamu ubah, jadi **jangan ditimpa**; cukup tempel method di bawah |
| **UPDATE** | `app/Providers/AppServiceProvider.php` | Mendaftarkan empat observer |
| **UPDATE** | `resources/js/composables/usePageData.js` | Tambah fungsi `preview()` |
| **UPDATE** | `resources/js/Pages/Submissions/Show.vue` | Tombol "Pratinjau" untuk berkas PDF |