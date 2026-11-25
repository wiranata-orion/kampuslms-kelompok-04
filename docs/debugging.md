# Debugging

## Reset kata sandi dapat dilakukan tanpa verifikasi identitas

**Gejala:** Pengguna yang hanya mengetahui NIM/NIP atau email dapat mengirim
request tanpa autentikasi ke `POST /api/v1/auth/reset-password` dan mengganti
kata sandi akun tersebut. Endpoint `check-user` juga mengungkapkan apakah
identifier terdaftar.

**Cara ditemukan:** Riwayat repository menunjukkan feature test sebelumnya
mengirim request guest dengan NIM dan kata sandi baru, lalu memastikan kata
sandi akun berubah. Kode handler saat itu mencari akun berdasarkan identifier
dan langsung menyimpan kata sandi baru tanpa bukti verifikasi melalui email
atau faktor lain. Perubahan pengamanan ini tercatat pada commit
`ff420399` (`fix: tutup reset kata sandi tanpa verifikasi`).

**Perbaikan:** Route guest `check-user` dan `reset-password` dihapus. Alur
reset langsung dinonaktifkan sampai tersedia mekanisme verifikasi yang aman.
Saat ini kedua endpoint mengembalikan `404`.

**Pencegahan regresi:** `test_password_reset_endpoints_are_not_exposed_without_verification`
di `tests/Feature/ApiContractTest.php` memastikan endpoint guest tidak tersedia.
