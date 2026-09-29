# Catatan Minggu 3

Nama : Jeshua Austin Daceka
NIM : 10241037
Kelas : A

## 3.3 Read → Break → Fix → Build

### READ: Bedah instalasi sendiri (45 menit)

**1. ERD**

![ERD KampusLMS](isi-path-gambar-ERD-kamu)

**2. Perilaku `onDelete` tiap foreign key beserta alasannya**

- `courses.lecturer_id` → `users.id`: **restrictOnDelete**
  Database menolak penghapusan akun dosen selama akun itu masih tercatat sebagai pengajar di suatu mata kuliah. Dengan begitu tidak ada mata kuliah yang kehilangan pengajarnya secara diam-diam.

- `materials.course_id` → `courses.id`: **cascadeOnDelete**
  Materi hanya bermakna di dalam mata kuliahnya. Kalau mata kuliahnya dihapus, materinya ikut dihapus otomatis oleh database.

- `assignments.course_id` → `courses.id`: **cascadeOnDelete**
  Alasannya sama dengan materi: tugas tidak berguna tanpa mata kuliah yang menaunginya.

- `submissions.assignment_id` → `assignments.id`: **cascadeOnDelete**
  Pengumpulan tugas mahasiswa tidak relevan lagi kalau tugasnya sudah dihapus.

- `grades.submission_id` → `submissions.id`: **cascadeOnDelete**
  Nilai menempel ke satu pengumpulan tugas. Kalau pengumpulannya hilang, nilainya ikut hilang supaya tidak ada nilai yatim.

**3. Kalau dosen dihapus, apa yang terjadi pada mata kuliahnya?**

Penghapusan dosen ditolak selama dia masih mengajar minimal satu mata kuliah. Dosen baru bisa dihapus setelah mata kuliahnya dipindahkan ke dosen lain atau dihapus lebih dulu. Ini sengaja dirancang supaya satu penghapusan akun tidak menyebabkan mata kuliah, materi, tugas, dan nilai hilang berantai.

**4. Kenapa `grades.submission_id` unique, bukan index biasa?**

Karena satu pengumpulan tugas hanya boleh punya satu nilai (relasi one-to-one). Index biasa hanya mempercepat pencarian dan tetap mengizinkan dua baris nilai untuk submission yang sama. Constraint unique membuat database sendiri yang menolak duplikasi itu.

---

### BREAK: Lima kerusakan (45 menit)

| # | Yang dicoba | Yang saya amati |
|---|-------------|-----------------|
| 1 | Hapus `unique(['course_id','user_id'])` dari `course_user`, lalu daftarkan mahasiswa yang sama dua kali | Mahasiswa yang sama bisa terdaftar dua kali di satu mata kuliah tanpa error, sehingga data jadi ganda. |
| 2 | Tambahkan `role` ke `$fillable` model `User`, lalu kirim request buat user dengan `role=admin` lewat form yang tidak punya field role | Mass assignment terjadi: nilai `role` dari request ikut tersimpan, jadi pengguna biasa bisa mengangkat dirinya sendiri menjadi admin. |
| 3 | Ganti seluruh `$fillable` dengan `protected $guarded = [];`, lalu ulangi nomor 2 | Hasilnya sama seperti nomor 2, bahkan lebih parah karena semua kolom bisa diisi dari request. Karena itu `$guarded` kosong dilarang. |
| 4 | Kosongkan isi `down()` di satu migrasi, lalu jalankan `php artisan migrate:refresh` | Tabel dari migrasi itu tidak ikut di-drop, sehingga saat `migrate` ulang muncul error tabel sudah ada. Migrasi tidak reversible dan CI jadi merah. |
| 5 | Ubah `restrictOnDelete` pada `lecturer_id` menjadi `cascadeOnDelete`, lalu hapus satu dosen | Semua mata kuliah milik dosen itu ikut terhapus, lalu materi, tugas, dan nilainya juga ikut hilang karena cascade berantai. |

---

### FIX

Temuan 1:
- Gejala: [isi]
- Penyebab: [isi]
- Perbaikan: [isi]

Temuan 2:
- Gejala: [isi]
- Penyebab: [isi]
- Perbaikan: [isi]

---

### BUILD: Kerangka KampusLMS

- [ ] Seluruh migrasi sesuai Bagian 4 spesifikasi, termasuk semua constraint dan index. Reversible.
- [ ] Seluruh model dengan relasi lengkap sesuai Bagian 4.3, `$fillable` yang ketat, `casts()` sebagai method.
- [ ] Factory dan seeder yang memenuhi Bagian 4.4, termasuk 3 akun demo.
- [ ] CRUD Mata Kuliah berfungsi penuh (index, create, store, show, edit, update, destroy).
- [ ] CRUD Pengguna berfungsi penuh, dengan `role` tidak di `$fillable` melainkan diisi eksplisit di controller.
- [ ] CI GitHub Actions aktif dan hijau: `migrate:fresh --seed` sukses, `migrate:refresh` sukses, `.env` tidak ter-commit.
- [ ] Setiap anggota punya commit atas namanya sendiri, dan setiap fitur masuk lewat PR yang direview anggota lain.

---

