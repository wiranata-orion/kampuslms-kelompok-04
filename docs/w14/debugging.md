
## Bukti regresi alur mata kuliah, materi, tugas, dan penilaian

**Gejala:**
1. Celah otorisasi pada alur ini adalah dosen dapat mengirim request langsung
dengan ID mata kuliah milik dosen lain, lalu materi atau nilai tersimpan meskipun
tombolnya tidak tersedia di UI.
2. Notifikasi adalah mahasiswa penerima tidak
menerima notifikasi setelah tugas dipublikasikan atau nilai diumumkan.

**Buat nyari penyebabnya**,
1. Telusuri request API sampai ke authorization pada Form Request atau policy dan pastikan relasi mata kuliah/submission berasal dari record server, bukan dari identitas yang dipercaya dari payload. 
2. Untuk notifikasi, periksa event publikasi tugas, listener, dan proses publikasi nilai; verifikasi penerima berdasarkan enrollment atau pemilik submission. Test berikut mengirim request langsung ke endpoint API, memeriksa `403` dengan `assertForbidden()`, serta memeriksa notifikasi dengan `Notification::fake()` dan `Notification::assertSentTo()`.

| Nama test | Kondisi awal | Aksi | Hasil yang diharapkan |
|---|---|---|---|
| `test_admin_successfully_creates_course` | Admin dan dosen pengampu valid tersedia; kode mata kuliah belum dipakai. | Admin mengirim `POST /api/v1/courses`. | Respons `201` dan mata kuliah tersimpan dengan dosen pengampu yang dipilih. |
| `test_course_lecturer_successfully_uploads_material` | Dosen mengampu mata kuliah aktif; storage lokal di-fake. | Dosen mengunggah PDF melalui `POST /api/v1/courses/{course}/materials`. | Respons `201`, materi terhubung ke mata kuliah dan uploader yang benar, berkas ada di storage privat. |
| `test_other_lecturer_is_forbidden_from_uploading_material` | Mata kuliah dimiliki dosen pertama; dosen kedua terautentikasi. | Dosen kedua mengunggah PDF ke endpoint materi mata kuliah tersebut. | `assertForbidden()`; record materi dan berkas tidak dibuat. |
| `test_course_lecturer_successfully_grades_submission` | Submission mahasiswa berada pada mata kuliah yang diampu dosen. | Dosen mengirim nilai lewat `PUT /api/v1/submissions/{submission}/grade`. | Respons `201` dan nilai tersimpan untuk submission oleh dosen pengampu. |
| `test_other_lecturer_is_forbidden_from_grading_submission` | Submission berada pada mata kuliah dosen pertama; dosen kedua terautentikasi. | Dosen kedua mengirim nilai ke submission tersebut. | `assertForbidden()`; tidak ada nilai yang tersimpan. |
| `test_published_assignment_notifies_enrolled_students` | Mahasiswa terdaftar pada mata kuliah; mahasiswa lain tidak terdaftar. | Dosen membuat tugas berstatus `published` lewat API. | `NewAssignmentNotification` terkirim ke mahasiswa terdaftar saja, dibuktikan dengan `Notification::assertSentTo()`. Tugas draft tidak memicu notifikasi publikasi. |
| `test_published_grade_notifies_only_its_submission_student` | Nilai belum dipublikasikan untuk submission mahasiswa. | Dosen mempublikasikan nilai lewat `POST /api/v1/grades/{grade}/publish`. | `GradePublishedNotification` terkirim ke pemilik submission saja, dibuktikan dengan `Notification::assertSentTo()`. |

Test tersebut menjadi pencegahan regresi: 
pemeriksaan forbidden memastikan otorisasi ditegakkan di server dan tidak bergantung pada UI, sementara fake notifikasi menguji integrasi endpoint, event/publikasi, listener, dan target penerima tanpa mengirim notifikasi sungguhan. 

Nilai baru memberi notifikasi saat dipublikasikan, sehingga mahasiswa tidak diberi tahu tentang nilai yang belum dapat dilihat.
