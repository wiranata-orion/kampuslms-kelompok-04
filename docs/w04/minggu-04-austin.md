# Catatan Minggu 4

Nama : Jeshua
NIM : 10241037
Kelas : A

## 4.3 Read → Break → Fix → Build

### READ: Telusuri satu siklus form gagal (30 menit)

**1. Method apa yang menerima request? Di controller mana?**

Saat form tambah mata kuliah dikirim, yang menerima adalah `CourseController::store()`. Saat form edit dikirim, yang menerima adalah `CourseController::update()`. Di `routes/web.php`, `POST /courses` dipetakan ke `CourseController@store` dan `PUT/PATCH /courses/{course}` ke `CourseController@update`. Controllernya ada di `app/Http/Controllers/CourseController.php`.

**2. Validasi terjadi sebelum atau sesudah baris pertama method controller?**

Sebelum. Signature method-nya `store(StoreCourseRequest $request)`, jadi Laravel membuat dan menjalankan `StoreCourseRequest` lebih dulu (`authorize()` lalu `rules()`). Baris pertama di dalam method, `$validated = $request->validated();`, baru berjalan kalau validasi lolos. Aturan untuk `sks` ada di `app/Http/Requests/StoreCourseRequest.php`.

**3. Ke mana Laravel me-redirect setelah gagal? Siapa yang menentukan tujuannya?**

Kembali ke halaman sebelumnya (yaitu form `courses/create`), dengan membawa pesan error dan input lama. Tujuannya ditentukan oleh mekanisme `FormRequest` bawaan Laravel (melalui `ValidationException`), bukan oleh kode di controller. Karena kami tidak mengubah `redirect` atau `redirectTo` di request ini, yang dipakai adalah perilaku default.

**4. Dari mana `@error('sks')` mengambil pesannya?**

Dari variabel `$errors` yang diisi Laravel ke session setelah validasi gagal. `@error('sks')` sama fungsinya dengan mengambil pesan pertama untuk field `sks` dari `$errors`. Teks pesannya berasal dari `messages()` di `StoreCourseRequest`, misalnya `'sks.between' => 'SKS harus antara 1 sampai 6.'`. Kalau tidak ada pesan custom, dipakai pesan bawaan Laravel.

**5. Dari mana `old('sks')` mengambil nilainya? Berapa lama bertahan?**

Dari input request sebelumnya yang disimpan Laravel ke session sebagai flash data saat redirect karena validasi gagal (`withInput()`). Nilai ini hanya bertahan untuk satu request berikutnya, yaitu saat form ditampilkan ulang. Kalau halaman dimuat ulang lagi, nilainya sudah hilang. Batas `SESSION_LIFETIME=120` menit hanya berlaku untuk umur session-nya, bukan untuk umur `old()`.

**6. Nama cookie session Laravel di DevTools**

`[isi sesuai yang kamu lihat di DevTools]`. Di Laravel 12 namanya diturunkan dari `APP_NAME`, dengan format seperti `laravel-session` atau `nama-app-session`, sesuai konfigurasi `cookie` di `config/session.php`.

---

### BREAK: Tujuh kerusakan (45 menit)

Uji coba lewat `curl`:

```bash
curl -X POST http://kampuslms.test/courses \
  -H "X-CSRF-TOKEN: <ambil dari halaman>" \
  -b cookies.txt \
  -d "code=XX01" -d "name=Uji" -d "sks=3" \
  -d "lecturer_id=99999" -d "status=superadmin"
```

| # | Yang dirusak | Prediksi saya | Yang saya amati |
|---|--------------|---------------|-----------------|
| 1 | Hapus `@csrf` dari form, lalu kirim | [isi] | Muncul error `419 Page Expired`. CSRF token mencegah pihak luar mengirim request palsu atas nama pengguna yang sedang login dari halaman yang bukan milik aplikasi. |
| 2 | Ganti `$request->validated()` menjadi `$request->all()`, lalu kirim field liar lewat `curl` | [isi] | Field di luar aturan validasi ikut diteruskan ke model, sehingga mass assignment terbuka lagi. Kolom yang tidak seharusnya diisi pengguna bisa ikut tersimpan. |
| 3 | Hapus validasi `exists:users,id` pada `lecturer_id`, kirim `lecturer_id=99999` | [isi] | Nilai `99999` diterima dan masuk ke database, padahal dosen dengan id itu tidak ada. Terbentuk data yatim yang tidak konsisten. |
| 4 | Hapus validasi `in:...` pada `status`, kirim `status=superadmin` | [isi] | Nilai `superadmin` lolos, padahal status hanya boleh `draft`, `active`, atau `archived`. Nilai enum jadi bisa dijebol. |
| 5 | Hapus `->withQueryString()`, lakukan pencarian lalu klik halaman 2 | [isi] | Kata kunci pencarian hilang di halaman 2 dan hasilnya kembali ke daftar tanpa filter. Pagination harus membawa parameter query. |
| 6 | Ganti `return redirect()` menjadi `return view()` pada `store`, lalu tekan F5 setelah simpan | [isi] | Browser mengirim ulang POST yang sama sehingga data tersimpan dua kali. Inilah alasan pola Post/Redirect/Get (PRG) dipakai. |
| 7 | Hapus `old(...)` dari semua input, lalu kirim form dengan satu kesalahan | [isi] | Setelah gagal, semua kolom kosong dan saya harus mengisi ulang dari awal. Pengalaman pengguna buruk dan peluang salah ketik bertambah. |

**Kesimpulan:** `@csrf`, `FormRequest`, validasi tiap field, redirect setelah POST, dan `old()` bekerja bersama supaya form aman, datanya konsisten, dan nyaman dipakai.