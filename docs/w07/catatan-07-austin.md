# Catatan Minggu 7

Nama : Jeshua Austin Daceka  
NIM : 10241037  
Kelas : A

### READ

**1. Proses login**

Proses login ditangani oleh `AuthController` melalui method `login()` di `app/Http/Controllers/AuthController.php`. Route POST-nya didaftarkan di `routes/web.php`.

**2. Pemanggilan autentikasi**

`Auth::attempt()` dipanggil di dalam method `login()` untuk memeriksa kecocokan email dan password pengguna. Opsi `remember` juga diteruskan berdasarkan input form.

**3. Fungsi `session()->regenerate()`**

Kode ini dijalankan setelah login berhasil untuk membuat session ID baru. Tujuannya mencegah *session fixation*, yaitu penyalahgunaan session ID yang sudah ada sebelum autentikasi.

**4. Hash password**

Pengaturan hash password terdapat di `app/Models/User.php`, pada bagian `casts()`:

```php
'password' => 'hashed',
```

Cast tersebut membuat Laravel melakukan hashing ketika password diberikan melalui proses penyimpanan atau pembaruan atribut model.

**5. Perubahan cookie session**

Cookie session dapat diperiksa melalui DevTools sebelum dan sesudah login. Setelah login berhasil, session ID diperbarui melalui `session()->regenerate()`. Perubahan nilai cookie perlu dipastikan langsung saat pengujian karena cookie yang terlihat bergantung pada konfigurasi aplikasi.

**6. Kondisi setelah logout dan menekan tombol Back**

Browser mungkin menampilkan kembali halaman sebelumnya dari cache. Namun, sesi login sudah diakhiri sehingga halaman yang membutuhkan autentikasi seharusnya tidak dapat diakses kembali tanpa login.

Pada proses logout, `Auth::logout()` mengakhiri autentikasi, `session()->invalidate()` membatalkan sesi, dan `session()->regenerateToken()` membuat token CSRF baru.