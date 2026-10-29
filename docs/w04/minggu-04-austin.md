# Catatan Minggu 4

Nama : Jeshua Austin Daceka  
NIM : 10241037  
Kelas : A

## 4.3 Read → Break → Fix → Build

### READ: Menelusuri proses validasi form

**1. Method yang menangani pengiriman form**

Form tambah mata kuliah diproses oleh method `store()` pada `CourseController`, sedangkan form edit mata kuliah diproses oleh method `update()`. Keduanya dihubungkan dengan route yang sesuai di `routes/web.php`.

**2. Kapan validasi dijalankan?**

Validasi dijalankan sebelum isi method controller diproses. Hal ini karena parameter method menggunakan `StoreCourseRequest` atau Form Request lain yang sesuai. Laravel memeriksa aturan validasi terlebih dahulu, sehingga data baru diteruskan ke controller jika memenuhi aturan yang ditentukan.

**3. Tujuan redirect ketika validasi gagal**

Jika input tidak memenuhi aturan validasi, Laravel akan mengembalikan pengguna ke halaman sebelumnya. Laravel juga membawa informasi kesalahan dan input yang sebelumnya dikirim. Dengan begitu, pengguna bisa memperbaiki form tanpa harus memulai pengisian dari awal.

**4. Cara kerja `@error('sks')`**

`@error('sks')` digunakan untuk menampilkan pesan kesalahan khusus pada field `sks`. Pesan tersebut berasal dari hasil validasi Laravel yang disimpan dalam error bag. Jika nilai SKS tidak sesuai aturan, misalnya di luar rentang yang diperbolehkan, pesan kesalahan dapat ditampilkan di dekat input tersebut.

**5. Fungsi `old('sks')`**

`old('sks')` digunakan untuk mengisi kembali field dengan nilai yang sebelumnya dikirim pengguna ketika validasi gagal. Nilai ini berasal dari input lama yang disimpan sementara melalui session flash. Dengan begitu, pengguna tidak perlu mengetik ulang semua data yang sudah benar.

**6. Cookie session Laravel**

Cookie session digunakan untuk membantu Laravel mengenali session pengguna di antara request. Nama cookie yang digunakan perlu diperiksa melalui DevTools pada bagian Application → Cookies. Pada konfigurasi default Laravel, nama cookie biasanya `laravel_session`, tetapi nama yang sebenarnya perlu dicocokkan dengan konfigurasi aplikasi.

---

### BREAK: Tujuh kerusakan

| No. | Perubahan yang dicoba | Hasil yang perlu diamati |
|---|---|---|
| 1 | Menghapus `@csrf` dari form | Request ditolak karena token CSRF tidak tersedia atau tidak valid. |
| 2 | Mengganti `$request->validated()` dengan `$request->all()` | Data yang tidak seharusnya diterima dapat ikut diproses jika tidak ada perlindungan lain. |
| 3 | Menghapus validasi `exists` pada `lecturer_id` | ID dosen yang tidak tersedia berpotensi diterima oleh aplikasi, meskipun constraint database masih bisa menolaknya. |
| 4 | Menghapus validasi nilai yang diizinkan untuk `status` | Nilai status di luar pilihan yang ditentukan berpotensi masuk ke proses penyimpanan. |
| 5 | Menghapus `withQueryString()` pada pagination | Parameter pencarian tidak lagi otomatis dibawa ketika berpindah halaman. |
| 6 | Mengganti redirect setelah penyimpanan dengan `return view()` | Refresh halaman dapat mengirim ulang request POST dan berpotensi membuat data duplikat. |
| 7 | Menghapus `old()` dari input form | Input yang sebelumnya sudah diisi tidak otomatis muncul kembali setelah validasi gagal. |

### Catatan hasil observasi

1. **Perlindungan CSRF:** Setelah `@csrf` dihapus, request form dapat ditolak dengan status `419` karena token CSRF tidak tersedia atau tidak sesuai. Hal ini menunjukkan bahwa form web Laravel membutuhkan token yang valid untuk request yang dilindungi CSRF.

2. **Penggunaan `validated()`:** Menggunakan `$request->all()` membuat controller menerima seluruh input request, bukan hanya data yang lolos validasi Form Request. Karena itu, penggunaan `validated()` membantu membatasi data yang diproses sesuai aturan validasi.

3. **Validasi `lecturer_id`:** Aturan `exists` memastikan ID dosen yang dikirim memang tersedia di tabel yang diperiksa. Jika aturan ini dihapus, ID yang tidak valid bisa melewati validasi aplikasi, tetapi hasil akhirnya tetap bergantung pada constraint database dan proses penyimpanan.

4. **Validasi `status`:** Pembatasan nilai status diperlukan agar data yang disimpan tetap sesuai pilihan yang didukung aplikasi. Tanpa pembatasan tersebut, nilai yang tidak dikenal bisa diteruskan ke proses berikutnya apabila tidak ada pemeriksaan tambahan.

5. **Pagination:** `withQueryString()` membantu mempertahankan parameter URL, termasuk kata kunci pencarian, saat pengguna berpindah halaman. Jika dihapus, pengguna bisa kehilangan filter yang sebelumnya digunakan.

6. **Redirect setelah POST:** Redirect setelah proses penyimpanan menerapkan pola Post/Redirect/Get (PRG). Pola ini membantu mencegah pengiriman ulang form saat halaman di-refresh, meskipun bukan jaminan mutlak bahwa duplikasi tidak akan terjadi.

7. **Penggunaan `old()`:** Tanpa `old()`, nilai input sebelumnya tidak otomatis ditampilkan kembali setelah validasi gagal. Pengguna perlu mengisi ulang field yang kosong atau kehilangan nilai yang sebelumnya sudah dimasukkan.

### FIX

**Temuan 1:**
- Gejala: [Isi berdasarkan masalah yang ditemukan.]
- Penyebab: [Jelaskan penyebabnya berdasarkan kode.]
- Perbaikan: [Tuliskan file dan perubahan yang dilakukan.]

**Temuan 2:**
- Gejala: [Isi berdasarkan masalah yang ditemukan.]
- Penyebab: [Jelaskan penyebabnya berdasarkan kode.]
- Perbaikan: [Tuliskan file dan perubahan yang dilakukan.]

---

### BUILD: Kerangka KampusLMS

- [ ] Struktur migrasi sesuai spesifikasi, termasuk foreign key, constraint, dan index.
- [ ] Model memiliki relasi yang sesuai serta pengaturan mass assignment yang aman.
- [ ] Factory dan seeder dapat menghasilkan data pengujian yang dibutuhkan.
- [ ] CRUD mata kuliah berjalan untuk proses melihat, menambah, mengubah, dan menghapus data.
- [ ] CRUD pengguna berjalan dengan pengaturan role yang tidak dapat diubah sembarangan melalui input pengguna.
- [ ] Pengujian migrasi berhasil dan konfigurasi rahasia tidak ikut diunggah ke repository.
- [ ] Perubahan fitur diperiksa melalui pengujian dan proses review sebelum digabungkan ke branch utama.