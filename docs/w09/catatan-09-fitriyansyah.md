## 9.3 Read  → Break → Fix → Build
**Target akhir minggu**:  Dosen bisa mengunggah materi, mahasiswa bisa mengumpulkan tugas, dan tidak seorang pun bisa mengunduh berkas milik orang lain.

### READ

Unggah satu berkas materi:

1. Temukan berkas fisiknya di sistem berkas. Path lengkapnya apa?
2. Buka baris database yang bersangkutan. Bandingkan `file_path` dengan lokasi fisiknya.
3. Coba akses berkas itu langsung lewat browser dengan menebak URL-nya. Berhasil atau tidak? Kenapa?
4. Buka `config/filesystems.php.` Identifikasi disk `local` dan `public` — apa persis bedanya?
5. Jalankan `php artisan storage:link`. Apa yang dibuatnya? Kenapa materi Anda tidak boleh ada di sana?

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