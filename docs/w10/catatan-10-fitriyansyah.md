## 10.3 Read  → Break → Fix → Build
**Target akhir minggu**:  

### READ
Setelah Event dan Listener pertama jadi, tanpa AI:

1. Matikan worker. Buat satu tugas baru. Buka tabel `jobs` — apa isinya?
2. Baca kolom `payload`. Bisakah Anda mengenali nama Listener dan ID tugasnya?
3. Jalankan `php artisan queue:work`, amati keluarannya. Periksa tabel `jobs` lagi.
4. Periksa tabel `notifications`. Berapa baris yang muncul? Cocok dengan jumlah mahasiswa?
5. Baca kolom `data` pada satu notifikasi. Adakah yang tidak seharusnya tersimpan di situ?

### BREAK
| # | Yang dicoba | Yang harus Anda amati |
|---|-------------|------------------------|
| 1 | Set `QUEUE_CONNECTION=database`, jangan jalankan worker, buat tugas baru | **Gagal total tanpa satu pun pesan error** |
| 2 | Hapus `implements ShouldQueue` dari Listener, buat tugas untuk 40 mahasiswa | Rasakan sendiri lamanya |
| 3 | Ganti `QUEUE_CONNECTION=sync`, ulangi nomor 2 | Bandingkan dengan nomor 1 dan 2 |
| 4 | Buat Notification melempar exception, jalankan worker | Amati `tries` dan tabel `failed_jobs` |
| 5 | Hentikan worker di tengah pemrosesan job | Job kembali ke queue atau hilang? |
| 6 | Masukkan nilai mahasiswa ke payload notifikasi, lalu buka tabel `notifications` sebagai admin | Kebocoran data permanen |
| 7 | Panggil endpoint "tandai dibaca" dengan ID notifikasi orang lain | IDOR versi notifikasi |
| 8 | Kalau memakai Reverb: ganti `PrivateChannel` menjadi `Channel`, langganan channel orang lain | Penyadapan realtime |

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