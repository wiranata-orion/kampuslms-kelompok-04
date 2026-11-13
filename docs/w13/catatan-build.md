# Rancangan implementasi presensi dan jadwal

## Aturan fitur

- Admin dan dosen pengampu dapat mengelola pola jadwal mingguan per mata kuliah; mahasiswa terdaftar dapat melihat jadwal dan mengisi presensi.
- Setiap pola memuat hari, jam mulai/selesai, ruang, serta tanggal mulai/akhir semester. Saat pola disimpan, sistem membentuk sesi bertanggal untuk tiap pertemuan, tanpa membuat duplikat saat pola disunting.
- Jadwal ditolak jika bertabrakan pada waktu dan rentang semester yang beririsan dengan jadwal dosen pengampu atau ruang yang sama.
- Respons bentrok menjelaskan mata kuliah, dosen/ruang penyebab, hari dan rentang waktunya; sistem menyertakan hingga tiga slot alternatif yang tersedia.
- Waktu input dan tampilan jadwal memakai zona Asia/Makassar (WITA); waktu sesi disimpan sebagai timestamp.
- Kode dan QR berlaku hanya selama sesi. Satu kode deterministik per sesi digunakan bersama, bukan dibuat per mahasiswa/pemindaian atau disimpan sebagai record token terpisah.
- Mahasiswa harus login dan terdaftar pada mata kuliah. Presensi ditolak jika kode salah/kedaluwarsa atau mahasiswa sudah tercatat pada sesi tersebut.
- Tidak ada rekap absensi per sesi di UI/API. Data dasar kehadiran tetap disimpan satu record per mahasiswa per sesi untuk deduplikasi dan riwayat.

## To-do implementasi

1. Tambahkan tabel pola jadwal mingguan, sesi pertemuan, dan record presensi.
2. Buat API untuk mengelola pola jadwal serta membaca daftar sesi; validasi bentrok di server dan berikan alasan beserta saran waktu lain.
3. Generate sesi tanggal semester secara idempoten ketika pola dibuat atau diperbarui; pertahankan catatan lama dan batalkan sesi mendatang yang tidak lagi berlaku.
4. Buat API untuk menampilkan QR/kode dosen dan menerima presensi mahasiswa; verifikasi role, pendaftaran, waktu sesi, dan duplikasi.
5. Buat UI Vue pengelolaan pola jadwal, daftar sesi, tampilan kode/QR dosen, dan input kode mahasiswa. Jangan tampilkan rekap per sesi.
6. Tambahkan tes untuk generasi sesi, bentrok dan sarannya, akses role, kode di luar waktu sesi, peserta bukan anggota, dan presensi ganda.
7. Rencana IoT tahap terpisah: reader kartu mengirim identitas kartu terdaftar dan ID sesi lewat API dengan kredensial perangkat; server menggunakan validasi sesi/peserta yang sama, lalu mencatat satu presensi dengan kanal IoT.

## Penghematan pembuatan kode/QR

- Token dihitung secara deterministik dari ID dan rentang waktu sesi menggunakan HMAC server. Token yang sama dapat diminta ulang selama sesi aktif, sehingga refresh halaman tidak membuat token/record baru.
- QR berisi tautan sesi beserta kode yang sama; QR dibuat dan di-cache di sisi klien saat halaman dosen aktif. Mahasiswa yang membuka tautan tetap harus login dan mengonfirmasi presensi. Satu QR dipakai bersama seluruh mahasiswa selama sesi, bukan dibuat ulang untuk setiap presensi.
- Presensi tetap memiliki satu baris per mahasiswa per sesi dengan batas unik database; tidak ada tabel ringkasan atau salinan rekap per sesi.

New file:
1. 2026_11_13_000001_create_course_schedules_and_attendances_tables.php
2. app/models/CourseSchedule.php
3. app/models/CourseSession.php
4. app/models/AttendanceRecord.php
5. app/Https/request/StoreCourseScheduleRequest.php
6. app/Https/request/UpdateCourseScheduleRequest.php
7. app/Https/resource/CourseScheduleResource.php
8. app/Https/resource/CourseSessionResource.php
9. app/services/CourseScheduleService.php
10. app/Services/AttendanceCodeService.php
11. app/Http/Controllers/Api/CourseScheduleController.php
12. app/Http/Controllers/Api/CourseSessionController.php
13. resources/js/Pages/Courses/Schedule.vue
14. test/Feature/CourseScheduleAttendanceTest.php


File update:
1. app/models/Course.php
2. app/models/User.php
3. app/policies/CoursePolicy.php
4. routes/api.php
5. resources/js/router.js
6. resources/js/Page/Courses/Show.vue
7. config/lms.php
8. resources/js/composables/usePageData.js
