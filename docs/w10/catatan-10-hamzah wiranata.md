### READ — Ikuti satu job (30 menit)

Setelah Event dan Listener pertama jadi, tanpa AI:

1. Matikan worker. Buat satu tugas baru. Buka tabel `jobs` — apa isinya?
2. Baca kolom `payload`. Bisakah Anda mengenali nama Listener dan ID tugasnya?
3. Jalankan `php artisan queue:work`, amati keluarannya. Periksa tabel `jobs` lagi.
4. Periksa tabel `notifications`. Berapa baris yang muncul? Cocok dengan jumlah mahasiswa?
5. Baca kolom `data` pada satu notifikasi. Adakah yang tidak seharusnya tersimpan di situ?

### JAWABAN

1. Saat diperiksa, `jobs` awalnya kosong. Saya membuat tugas terbit sementara di dalam transaksi database, lalu memicu `AssignmentPublished`. Sesudah tugas dibuat, tabel `jobs` berisi **1 baris** untuk listener antrean. Worker `queue:listen` dari `composer run dev` sedang hidup, jadi percobaan dilakukan dalam transaksi yang belum di-commit; worker tersebut tidak dapat mengambil job percobaan itu. Setelah pemeriksaan selesai, transaksi di-rollback.

2. Ya. Baris `jobs` menampilkan `App\Listeners\SendNewAssignmentNotifications` sebagai `displayName`. Objek `CallQueuedListener` dalam `payload` juga memuat listener tersebut dan event `AssignmentPublished`. ID assignment sementara yang terserialisasi adalah **16**.

3. Saya menjalankan `queue:work --once` pada job listener. Job itu selesai, lalu `jobs` berisi **16 job** pengiriman notifikasi—satu untuk setiap mahasiswa terdaftar di mata kuliah uji. Saya lanjutkan worker dalam mode `--once` sampai antrean kosong; setelah semua job diproses, `jobs` kembali menjadi **0**. Setelah itu transaksi di-rollback, sehingga tidak ada data percobaan yang tersimpan permanen.

4. Setelah antrean pengiriman notifikasi selesai, `notifications` berisi **16 baris**. Jumlah itu cocok dengan **16 mahasiswa** yang terdaftar di mata kuliah uji. Sesudah rollback, tabel kembali ke kondisi awal, yaitu **0 baris**.

5. Kolom `data` berisi `assignment_id`, `id`, `title`, `judul`, `course_name`, `nama_mata_kuliah`, `due_date`, `due_at`, `message`, `pesan`, dan `url`. Isinya hanya identitas dan informasi tugas yang dipakai UI; tidak ada password, token, atau path berkas. Beberapa field memang menyimpan nilai yang sama dalam dua nama, misalnya `title`/`judul` dan `message`/`pesan`, sehingga redundan tetapi bukan rahasia.