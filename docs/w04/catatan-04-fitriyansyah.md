## 4.3 Read → Break → Fix → Build

### READ — Telusuri satu siklus form gagal (30 menit)

1. Method apa yang menerima request? Di controller mana?
2. Di titik mana persisnya validasi terjadi — sebelum atau sesudah baris pertama method controller?
3. Ke mana Laravel me-redirect setelah gagal? Siapa yang menentukan tujuannya?
4. Dari mana `@error('sks')` mengambil pesannya?
5. Dari mana `old('sks')` mengambil nilainya? Berapa lama nilai itu bertahan?
6. Buka DevTools → Application → Cookies. Temukan cookie session Laravel. Catat namanya.

#### Jawaban

1. Method yang menerima request adalah `CourseController::store()` pada saat submit form tambah mata kuliah, dan `CourseController::update()` pada saat edit. Di route, `POST /courses` dipetakan ke `CourseController@store`, sedangkan `PUT/PATCH /courses/{course}` dipetakan ke `CourseController@update` di [routes/web.php](../../routes/web.php). Di controller, method tersebut ada di [app/Http/Controllers/CourseController.php](../../app/Http/Controllers/CourseController.php).

2. Validasi terjadi sebelum baris pertama method controller dieksekusi. Kenapa? Karena method signature-nya adalah `store(StoreCourseRequest $request)` dan Laravel memanggil `StoreCourseRequest` terlebih dahulu untuk menjalankan `authorize()` dan `rules()`. Hanya setelah validasi berhasil, baris pertama di dalam method (`$validated = $request->validated();`) baru dijalankan. Aturan untuk field `sks` ada di [app/Http/Requests/StoreCourseRequest.php](../../app/Http/Requests/StoreCourseRequest.php).

3. Jika validasi gagal, Laravel akan me-redirect kembali ke halaman sebelumnya (default `back()`) sambil membawa error bag dan input lama. Tujuannya ditentukan oleh mekanisme `FormRequest`/`ValidationException` Laravel, bukan oleh method controller secara langsung. Karena tidak ada override custom `redirect`/`redirectTo` di request ini, maka defaultnya kembali ke form yang mengirim request tersebut. Dalam aplikasi ini, form tambah mata kuliah ada di [resources/views/courses/create.blade.php](../../resources/views/courses/create.blade.php) dan route `courses.store` ada di [routes/web.php](../../routes/web.php).

4. `@error('sks')` mengambil pesan dari `$errors` yang dibuat Laravel setelah validasi gagal. Blade directive ini berfungsi seperti `$errors->first('sks')` atau `$errors->get('sks')`. Pesannya diambil dari error bag session yang diisi dari validator, dan untuk field `sks` pesan defaultnya berasal dari `messages()` di [app/Http/Requests/StoreCourseRequest.php](../../app/Http/Requests/StoreCourseRequest.php), misalnya `'sks.between' => 'SKS harus antara 1 sampai 6.'`.

5. `old('sks')` mengambil nilai lama dari input yang disimpan di session flash setelah validasi gagal. Laravel menyimpannya via `withInput()` pada redirect validation error, lalu `old()` membacanya dari session untuk request berikutnya. Nilai itu bertahan selama session masih valid; di aplikasi ini, `SESSION_LIFETIME=120` di [.env](../../.env) dan `lifetime` di [config/session.php](../../config/session.php), jadi umumnya bertahan sekitar 120 menit (bila session belum expired).

6. Cookie session Laravel yang terlihat di DevTools → Application → Cookies adalah `laravel_session`. Nama ini adalah default Laravel, sesuai konfigurasi `cookie` di [config/session.php](../../config/session.php). Karena aplikasi ini tidak mengubah `SESSION_COOKIE` di [.env](../../.env), maka cookie default-nya adalah `laravel_session`.

### BREAK — Tujuh kerusakan (45 menit)

Tulis prediksi lebih dulu, baru jalankan.

| # | Yang dirusak | Yang dipelajari |
|---|--------------|------------------------|
| 1 | Hapus `@csrf` dari form, lalu kirim | request ditolak dengan `419 | Page Expired` karena memang token CSRF untuk mencegah request palsu dari outpage yang sah. |
| 2 | Ganti `$request->validated()` menjadi `$request->all()`, lalu kirim field liar lewat `curl` | Data liar bisa masuk karena validasi tidak berjalan. Pengguna bebas mengisi data bahkan jika field datanya seharusnya gak boleh. |
| 3 | Hapus validasi `exists:users,id` pada `lecturer_id`, kirim `lecturer_id=99999` | - |
| 4 | Hapus validasi `in:...` pada `status`, kirim `status=superadmin` | - |
| 5 | Hapus `->withQueryString()`, lakukan pencarian lalu klik halaman 2 | Fitur pencarian ataupun pembatasan baris pada 1 halaman jadi menghilang. 1 halaman bisa menampilkan seluruh baris data.  |
| 6 | Ganti `return redirect()` menjadi `return view()` pada `store`, lalu tekan F5 setelah simpan | Datanya bisa tersimpan, dan saat menekan F5, form bisa dikirim ulang yang bisa menghasilkan duplikasi data. |
| 7 | Hapus `old(...)` dari semua input, lalu kirim form dengan satu kesalahan | Ketika validasi gagal karena ketidaksengajaan, pengguna jadi harus mengisi ulang semuanya. UX yang buruk adalah ketika penggna harus melakukan sesuatu dengan lebih pada hal yang tidak seharusnya. |
