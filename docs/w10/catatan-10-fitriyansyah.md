## 10.3 Read  → Break → Fix → Build
**Target akhir minggu**: Notifikasi KampusLMS berjalan lewat queue, dan mahasiswa mendapat pemberitahuan saat ada tugas baru atau nilai keluar.

### READ
Setelah Event dan Listener pertama jadi, tanpa AI:

1. Matikan worker. Buat satu tugas baru. Buka tabel `jobs` — apa isinya?
2. Baca kolom `payload`. Bisakah Anda mengenali nama Listener dan ID tugasnya?
3. Jalankan `php artisan queue:work`, amati keluarannya. Periksa tabel `jobs` lagi.
4. Periksa tabel `notifications`. Berapa baris yang muncul? Cocok dengan jumlah mahasiswa?
5. Baca kolom `data` pada satu notifikasi. Adakah yang tidak seharusnya tersimpan di situ?

#### Jawaban

1. Saya membuat tugas di dalam transaksi database, lalu `AssignmentPublished` jadi kepicu. Pas tugas sudah dibuat, tabel `jobs` berisi **1 baris** untuk listener antrean. Worker `queue:listen` dari `composer run dev` sedang hidup, jadi percobaan dilakukan dalam transaksi yang belum di-commit; worker tersebut tidak dapat mengambil job percobaan itu.

2. Bisa. Karena baris `jobs` menampilkan `App\Listeners\SendNewAssignmentNotifications` as `displayName`.

3. Saat menjalankan `queue:work`. Job-nya selesai, lalu `jobs` berisi pengiriman notifikasi untuk masing-masing mahasiswa terdaftar. Setelah diproses, `jobs` kembali menjadi 0 yang artinya semua berjalan.

4. Jumlah notifikasi selalu cocok dengan jumlah mahasiswa yang terdaftar.

5. Tidak ada isi yang tidak seharusnya tersimpan. Karena isinya hanya identitas dan informasi tugas yang dipakai UI.

### BREAK
| # | Yang dicoba | Yang dipelajari |
|---|-------------|------------------------|
| 1 | Set `QUEUE_CONNECTION=database`, jangan jalankan worker, buat tugas baru | **Gagal total tanpa satu pun pesan error** |
| 2 | Hapus `implements ShouldQueue` dari Listener, buat tugas untuk 40 mahasiswa | Job bisa keimplementasi dalam waktu yang lama, terlalu lama hanya untuk sekedar notifikasi yang malah diharapkan real-time. |
| 3 | Ganti `QUEUE_CONNECTION=sync`, ulangi nomor 2 | Job bisa langsung jalan tanpa antrian (queue) |
| 4 | Buat Notification melempar exception, jalankan worker | - |
| 5 | Hentikan worker di tengah pemrosesan job | Job menghilang |
| 6 | Masukkan nilai mahasiswa ke payload notifikasi, lalu buka tabel `notifications` sebagai admin | Bisa melihat data dari user |
| 7 | Panggil endpoint "tandai dibaca" dengan ID notifikasi orang lain | - |
| 8 | Kalau memakai Reverb: ganti `PrivateChannel` menjadi `Channel`, langganan channel orang lain | Ya, bisa melakukan penyadapan |

> Aplikasi yang "tidak error tapi tidak berfungsi" jauh lebih sulit didiagnosis daripada aplikasi yang jelas-jelas rusak, dan ini akan terjadi lagi di minggu 12 saat deployment.

### FIX

Branch `w10` pada repo `kampuslms-broken` berisi **7 masalah**: 
1. Listener tanpa `ShouldQueue`, 
2. notifikasi dikirim dalam loop satu per satu (N+1), 
3. payload memuat nilai mahasiswa, 
4. endpoint tandai-dibaca tanpa pemeriksaan kepemilikan, 
5. tidak ada `failed_jobs`, 
6. job tanpa `tries`, dan 
7. satu channel broadcast yang publik.