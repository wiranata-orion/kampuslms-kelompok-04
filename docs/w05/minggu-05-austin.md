# Catatan Minggu 5

Nama : Jeshua Austin Daceka  
NIM : 10241037  
Kelas : A

## 5.3 Read → Break → Fix → Build

**Target akhir minggu:** Merapikan susunan route KampusLMS, memahami penggunaan route model binding, dan mulai menerapkan middleware untuk mengatur akses.

### READ

**1. Hasil pemeriksaan route**

Berdasarkan perintah `php artisan route:list --except-vendor`, terdapat 17 route yang ditampilkan. Route tersebut mencakup halaman utama, mata kuliah, pengguna, dashboard, dan halaman tentang aplikasi.

**2. Route yang menggunakan parameter model**

Ada 8 route yang menggunakan parameter model, yaitu `{course}` dan `{user}`. Masing-masing kelompok memiliki empat route untuk melihat detail, mengubah data, menghapus data, dan membuka halaman edit.

**3. Pembagian hak akses**

Dari daftar route saja, belum bisa dipastikan apakah setiap route sudah memiliki perlindungan akses yang sesuai. Idealnya, admin dapat mengelola data sesuai kewenangannya, sedangkan dosen dan mahasiswa hanya dapat mengakses data yang memang menjadi hak mereka. Hal ini perlu diperiksa melalui konfigurasi middleware dan logika otorisasi pada aplikasi.

**4. Pengertian IDOR**

IDOR (*Insecure Direct Object Reference*) adalah celah keamanan yang terjadi ketika aplikasi tidak memeriksa hak akses terhadap objek yang diminta pengguna. Contohnya, seseorang mengganti ID pada URL untuk mencoba membuka data milik pengguna lain. Jika aplikasi tidak melakukan pengecekan kepemilikan atau izin akses, data tersebut berpotensi diakses tanpa hak.

**Daftar Titik Rawan IDOR**

| No. | Route | Potensi risiko |
|---|---|---|
| 1 | `courses/{course}` — `courses.show` | Pengguna dapat mencoba melihat detail mata kuliah yang bukan haknya. |
| 2 | `courses/{course}` — `courses.update` | Data mata kuliah berpotensi diubah tanpa izin yang sesuai. |
| 3 | `courses/{course}` — `courses.destroy` | Pengguna berpotensi menghapus mata kuliah milik pihak lain. |
| 4 | `courses/{course}/edit` — `courses.edit` | Halaman edit dapat dicoba diakses menggunakan ID mata kuliah lain. |
| 5 | `users/{user}` — `users.show` | Informasi pengguna lain berpotensi terbuka. |
| 6 | `users/{user}` — `users.update` | Data akun pengguna lain berpotensi diubah. |
| 7 | `users/{user}` — `users.destroy` | Akun pengguna lain berpotensi dihapus. |
| 8 | `users/{user}/edit` — `users.edit` | Halaman pengeditan akun lain berpotensi diakses. |

Route di atas menjadi kandidat pemeriksaan karena menerima parameter objek. Namun, keberadaan parameter tersebut tidak otomatis berarti aplikasinya rentan IDOR. Risiko sebenarnya bergantung pada pemeriksaan autentikasi, otorisasi, dan kepemilikan data yang diterapkan.

---

### BREAK

| No. | Pengujian | Yang saya amati / pelajari |
|---|---|---|
| 1 | Login sebagai mahasiswa A, lalu coba membuka submission milik mahasiswa B dengan mengganti ID pada URL. | Pengujian ini digunakan untuk mengetahui apakah aplikasi memeriksa kepemilikan submission, bukan hanya ID yang diminta. |
| 2 | Coba buka `/courses/1/assignments/99`, dengan asumsi tugas 99 bukan milik mata kuliah 1. | Saya perlu memastikan apakah tugas yang diminta benar-benar terkait dengan mata kuliah pada URL. |
| 3 | Aktifkan `Route::scopeBindings()`, kemudian ulangi pengujian nomor 2. | Saya mempelajari bahwa scoped binding dapat membatasi pencarian model berdasarkan relasinya dengan model induk pada URL. |
| 4 | Coba mendaftarkan middleware melalui `app/Http/Kernel.php` mengikuti tutorial Laravel versi lama. | Saya perlu mencocokkan cara pendaftaran middleware dengan struktur versi Laravel yang digunakan proyek. |
| 5 | Tambahkan middleware `role:admin` pada grup route, lalu coba mengaksesnya menggunakan akun dosen. | Pengujian ini bertujuan memeriksa apakah middleware menolak pengguna yang tidak memiliki role admin. |
| 6 | Login sebagai dosen A, kemudian coba mengedit mata kuliah milik dosen B. | Walaupun kedua akun memiliki role dosen, aplikasi tetap perlu memeriksa apakah dosen tersebut berhak mengubah mata kuliah yang dituju. |

**Catatan:** Hasil akhir setiap pengujian perlu disesuaikan dengan respons aplikasi yang benar-benar muncul saat dicoba, misalnya berhasil diakses, ditolak, atau menghasilkan error.

### FIX

**Temuan 1: Pemeriksaan akses terhadap data**

- **Gejala:** Pengguna mencoba membuka atau mengubah objek menggunakan ID yang bukan miliknya.
- **Penyebab:** Pemeriksaan role saja belum tentu menjamin bahwa pengguna berhak mengakses objek tertentu.
- **Perbaikan:** Tambahkan pemeriksaan otorisasi dan kepemilikan data pada proses yang relevan.

**Temuan 2: Hubungan antara model pada URL**

- **Gejala:** Parameter model anak dicoba menggunakan ID yang tidak sesuai dengan model induknya.
- **Penyebab:** Relasi antara model induk dan model anak perlu diperiksa agar objek yang diambil sesuai dengan URL.
- **Perbaikan:** Pertimbangkan penggunaan scoped route model binding dan pastikan relasi model sudah didefinisikan dengan benar.

**Temuan 3: Pendaftaran middleware**

- **Gejala:** Cara mendaftarkan middleware dari tutorial lama tidak sesuai dengan struktur proyek.
- **Penyebab:** Struktur konfigurasi middleware dapat berbeda antarversi Laravel.
- **Perbaikan:** Periksa mekanisme pendaftaran middleware yang sesuai dengan versi Laravel yang digunakan.

### BUILD

- [ ] Memeriksa kembali daftar route dan parameter model yang digunakan.
- [ ] Mendokumentasikan route yang perlu diuji terhadap IDOR.
- [ ] Menguji apakah pengguna dapat mengakses objek milik pengguna lain.
- [ ] Memeriksa penggunaan scoped route model binding pada route yang memiliki relasi model.
- [ ] Memastikan middleware diterapkan pada grup route yang sesuai.
- [ ] Mencatat hasil pengujian berdasarkan respons aplikasi yang sebenarnya.