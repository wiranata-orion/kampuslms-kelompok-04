## 3. Peran & Hak Akses

| Aksi | Admin | Dosen | Mahasiswa |
|------|:-----:|:-----:|:---------:|
| CRUD pengguna & role | ✔ | — | — |
| CRUD mata kuliah | ✔ | — | — |
| Kelola enrollment | ✔ | ✔ (MK sendiri) | — |
| CRUD materi | ✔ | ✔ (MK sendiri) | lihat/unduh |
| CRUD tugas | ✔ | ✔ (MK sendiri) | lihat |
| Mengumpulkan tugas | — | — | ✔ (MK yang diikuti) |
| Memberi nilai | — | ✔ (MK sendiri) | — |
| Melihat nilai orang lain | ✔ | ✔ (MK sendiri) | ✘ |

---

## Rancangan per Policy

CoursePolicy
viewAny: matriks diam. [Q1]
view: admin boleh semua MK. Dosen boleh MK miliknya (tersirat dari “MK sendiri”). Mahasiswa: [Q1].
🔗 Course::lecturer (kolom lecturer_id) dan Course::students (exists()).
create, update, delete: hanya admin. Dosen dan mahasiswa ditolak.
manageEnrollment (menambah, mengeluarkan, dan melihat peserta): admin boleh semua MK, dosen hanya MK miliknya, mahasiswa ditolak. Bentuk method ini [Q9].
🔗 Course::lecturer.

MaterialPolicy
viewAny (daftar materi dalam satu MK): admin boleh, dosen boleh bila pemilik MK, mahasiswa boleh bila peserta MK. Syarat “peserta” untuk mahasiswa perlu dikonfirmasi [Q2].
🔗 Course::lecturer dan Course::students, memakai MK induk yang dioper ke policy.
view (termasuk unduh): aturannya sama dengan viewAny, dihitung dari MK materi itu.
🔗 Material::course → lecturer_id atau students.
create, update, delete: admin boleh (sesuai matriks, bertentangan dengan kode saat ini [Q3]). Dosen boleh hanya untuk MK miliknya. Mahasiswa ditolak. Pemilik diukur dari MK atau dari pengunggah [Q4].
🔗 Material::course (dan Material::uploader jika Q4 memilih pengunggah).

AssignmentPolicy
viewAny, view: admin boleh. Dosen boleh bila pemilik MK. Mahasiswa boleh melihat tugas MK yang diikutinya. Soal tugas berstatus draft [Q5].
🔗 Assignment::course → lecturer_id atau students.
create, update, delete: admin boleh ([Q3]). Dosen boleh hanya untuk MK miliknya. Mahasiswa ditolak. Dasar kepemilikan [Q4].
🔗 Assignment::course. Untuk create, MK diambil dari course_id di request.

SubmissionPolicy
viewAny (daftar pengumpulan satu tugas): dosen boleh bila pemilik MK. Admin dan mahasiswa [Q6].
🔗 Assignment::course.
view (termasuk unduh): dosen boleh bila pemilik MK dan mahasiswa boleh pada pengumpulannya sendiri. Admin dan konfirmasi hak mahasiswa [Q6].
🔗 Submission::assignment → Assignment::course → lecturer_id, serta Submission::student (user_id).
create (mengumpulkan): hanya mahasiswa yang menjadi peserta MK tugas tersebut. Admin dan dosen ditolak (matriks “—”). Syarat status tugas dan tenggat [Q7].
🔗 Assignment::course → Course::students (exists()).
update, delete: matriks diam. [Q7]

GradePolicy
viewAny: matriks diam, dan tidak ada endpoint daftar nilai. [Q8]
view: admin boleh. Dosen boleh bila pemilik MK. Mahasiswa hanya nilai miliknya sendiri, yang tersirat dari “orang lain ✘” [Q6].
🔗 Grade::submission → Submission::assignment → Assignment::course → lecturer_id, serta Submission::user_id. Ini rantai tiga hop, jadi relasinya perlu di-eager-load.
create (memberi nilai, dioper Submission): hanya dosen pemilik MK dari submission itu. Admin dan mahasiswa ditolak (matriks “—”).
🔗 Submission::assignment → Assignment::course → lecturer_id.
update: diasumsikan tercakup “memberi nilai” untuk dosen pemilik MK, tapi perlu dikonfirmasi [Q8].
delete: matriks diam. [Q8]

UserPolicy
viewAny, view, create, update, delete: hanya admin (matriks “CRUD pengguna & role”). Hak melihat atau mengubah profil sendiri, larangan hapus diri sendiri, dan restore soft-delete [Q10].
Tidak perlu relasi, cukup role. Larangan hapus diri sendiri membandingkan id.
EnrollmentPolicy (opsional)

Perlu tidaknya policy ini [Q9].


### Akses umum

**1. Admin pada materi, tugas, pengumpulan, dan nilai.** Matriks dan kode berbeda.

- A. Ikuti matriks: admin boleh CRUD materi dan tugas, serta melihat nilai.
- B. Ikuti kode: admin ditolak di bagian itu.

**2. Tugas draft.**

- A. Mahasiswa tidak boleh melihat dan tidak boleh mengumpulkan ke tugas draft (kode).
- B. Draft tidak dibedakan di policy.

**3. Katalog MK.**

- A. Semua role hanya melihat MK miliknya.
- B. Dosen dan mahasiswa boleh melihat katalog semua MK.

**4. Status MK (`draft`, `active`, `archived`).**

- A. Tidak memengaruhi akses.
- B. Mahasiswa tidak boleh mengumpulkan tugas di MK `archived`.
- C. Mahasiswa tidak boleh melihat MK `draft` atau `archived`.

**5. Satu dosen per MK, dan “MK sendiri” berarti MK yang diampu (bukan materi yang dia unggah).**

- A. Ya.
- B. Tidak.

### Pengumpulan dan nilai

**6. Nilai milik sendiri bagi mahasiswa.**

- A. Terlihat begitu dosen memberi nilai (kode).
- B. Terlihat setelah dosen “mempublikasikan” (perlu kolom baru).
- C. Mahasiswa tidak boleh melihat nilai sama sekali.

**7. Melihat dan mengunduh berkas pengumpulan.**

- A. Admin dan dosen pengampu boleh (kode).
- B. Hanya dosen pengampu.

**8. Revisi pengumpulan oleh mahasiswa.**

- A. Boleh selama belum lewat tenggat, atau selama `allow_late` aktif (kode).
- B. Sama seperti A, tetapi terkunci setelah dinilai.
- C. Tidak boleh revisi.

**9. Tenggat pengumpulan.**

- A. Tetap validasi di Form Request, respons 422 (kode).
- B. Dipindah ke policy, respons 403.

**10. Hapus pengumpulan.**

- A. Tidak ada yang boleh (kode, belum ada route).
- B. Hanya admin.
- C. Admin dan dosen pengampu.

**11. Hapus nilai.**

- A. Tidak ada yang boleh (kode, belum ada route).
- B. Dosen pengampu.
- C. Admin dan dosen pengampu.

**12. Hapus tugas yang sudah punya pengumpulan atau nilai.**

- A. Boleh.
- B. Dilarang jika sudah ada pengumpulan.
- C. Dilarang hanya jika sudah ada nilai.

### MK dan enrollment

**13. Hapus MK yang masih punya enrollment, materi, atau tugas.**

- A. Boleh, data terkait mengikuti aturan FK database.
- B. Dilarang selama masih ada enrollment.
- C. Dilarang selama masih ada data terkait apa pun.

**14. Unenroll mahasiswa yang sudah punya pengumpulan atau nilai.**

- A. Boleh, datanya tetap tersimpan.
- B. Dilarang.

**15. Letak aturan enrollment.**

- A. Ability `manageEnrollment` di `CoursePolicy`.
- B. `EnrollmentPolicy` terpisah.

**16. Dosen melihat daftar mahasiswa MK-nya (sekarang hanya admin).**

- A. Ya.
- B. Tidak.

### Pengguna (UserPolicy)

**17. Apakah `UserPolicy` masuk lingkup?**

- A. Ya.
- B. Tidak, hanya lima model.

**18. Akun sendiri (`/me`, ganti nama atau password).**

- A. Diatur di policy.
- B. Tetap di luar policy.

**19. Admin menghapus akunnya sendiri.**

- A. Dilarang (kode).
- B. Boleh.

**20. Admin mengubah role dirinya sendiri.**

- A. Boleh.
- B. Dilarang.

**21. Mencegah kondisi tanpa admin (admin terakhir tidak boleh dihapus atau diturunkan).**

- A. Ya.
- B. Tidak.

**22. Hapus pengguna yang punya data terkait.**

- A. Boleh (soft delete).
- B. Dilarang jika dosen masih mengampu MK.
- C. Dilarang jika punya data terkait apa pun.

**23. Method tambahan di `UserPolicy`.**

- A. Cukup `delete`.
- B. Tambah `restore` (admin).
- C. Tambah `restore` dan `forceDelete` (admin).

### Implementasi

**24. Contoh pemanggilan di tempat ketiga.** Frontend-nya SPA Vue, dan Blade hanya membungkus satu view `app`.

- A. Tunjukkan contoh `@can` di Blade, plus cara mengirim flag izin ke Vue lewat API Resource.
- B. Hanya contoh `@can` di Blade.

1a, 2a, 3a, 4c, 5a, 6b, 7a, 8c, 9A 10C 11C 12C 13C 14A 15A 16A 17A 18A 19A 20B 21B 22B 23B 24A

Pertanyaan dan Policy mungkin memiliki perbedaan. Gunakan pertanyaan-pertanyaan tersebut untuk melengkapi spesifikasi policy.

---

Part Winata:
1. Autentikasi lengkap: login, logout, register (khusus admin yang mendaftarkan), reset kata sandi.
2. Tiga peran berfungsi dengan dashboard berbeda per peran.

Part Aji:
3. Policy untuk seluruh model (Course, Material, Assignment, Submission, Grade), dipanggil di controller dan dipakai di Blade.
4. Seluruh index disaring di level query sesuai peran.
5. Tabel Titik Rawan IDOR terisi penuh di docs/keamanan.md: tiap baris menyebut Policy atau query yang menutupnya.

Part Austin
6. CRUD Materi dan Tugas berfungsi dengan otorisasi yang benar (unggah berkas baru minggu 9 — cukup metadata dulu).
7. `scripts/test-authz.sh` — skrip pengujian otorisasi yang seluruhnya lolos.

