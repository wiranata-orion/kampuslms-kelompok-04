## File storage design

| Atribut | Foto Profil | Materi Kuliah (PDF/PPTX) | Pengumpulan Tugas (PDF/DOCX) |
|----------|------------|--------------------------|------------------------------|
| Disk | `public` | `local` (`storage/app/private`) | `local` (`storage/app/private`) |
| Alasan | Dipakai sebagai `<img>` di banyak halaman, dan SPA memakai bearer token yang tidak bisa dikirim oleh tag `<img>`. Sensitivitasnya rendah. | Hanya untuk peserta kelas. Hak akses berubah mengikuti pendaftaran, sehingga harus dicek setiap kali diunduh. | Memuat pekerjaan dan identitas mahasiswa. Hanya pemilik, dosen pengampu, dan admin yang boleh mengakses. |
| Struktur Folder | `avatars/{random}.jpg` | `materials/{course_id}/{random}.pdf` | `submissions/{assignment_id}/{random}.docx` |
| Nama Fisik | Acak (`hashName`). Nama asli tidak disimpan. | Acak. Nama asli disimpan pada `original_name`. | Acak. Nama asli disimpan pada `original_name`. |
| Cara Diakses | URL langsung `/storage/avatars/...` melalui symlink. Path disimpan di `users.avatar_path` dan dikirim sebagai `avatar_url`. | `GET /api/v1/materials/{material}/download`. Controller memanggil `Gate::authorize('download', $material)` lalu mengirim berkas sebagai stream. | `GET /api/v1/submissions/{submission}/download`. Controller memanggil `Gate::authorize('download', $submission)` lalu mengirim berkas sebagai stream. |
| Siapa yang Berhak | Melihat: siapa saja yang memiliki URL. Mengubah/menghapus: pemilik akun dan admin. | Admin, dosen pengampu mata kuliah, dan mahasiswa terdaftar dengan status `active`. Dosen lain dan mahasiswa yang tidak terdaftar akan mendapat `403`. | Pemilik submission, dosen pengampu mata kuliah asal tugas, dan admin. Mahasiswa lain ditolak, termasuk yang berada di kelas yang sama. |
| Validasi | `jpg`, `png`, `webp`, maksimal 2 MB, di-re-encode agar metadata EXIF hilang. | `pdf`, `ppt`, `pptx`, ukuran max: 20mb. | `mimes:pdf,doc,docx`, `max:5120`, `required`. |

