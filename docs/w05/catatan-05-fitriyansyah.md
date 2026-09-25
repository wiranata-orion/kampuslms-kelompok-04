## 5.3 Read → Break → Fix → Build
**Target akhir minggu**: Route KampusLMS tersusun rapi dalam grup, memakai route model binding, dan middleware pertama sudah terpasang.

### READ
1. Jalankan php artisan `route:list --except-vendor`. Salin keluarannya ke catatan.
2. Tandai setiap route yang menerima parameter model ({course}, {assignment}, dst).
3. Untuk setiap route bertanda, jawab: siapa saja yang seharusnya boleh mengaksesnya, dan apa yang saat ini mencegah orang lain?Kemungkinan besar jawabannya "belum ada apa-apa" — itu wajar, dan itulah pekerjaan minggu ini dan minggu 7.
4. Buat tabel di docs/minggu-05-<nama>.md berjudul "Daftar Titik Rawan IDOR". Tabel ini akan Anda pakai lagi di minggu 7 dan saat interview.

```shell
GET|HEAD        / ........................................................................................................................... routes/web.php:8
  GET|HEAD        courses ............................................................................................... courses.index › CourseController@index
  POST            courses ............................................................................................... courses.store › CourseController@store
  GET|HEAD        courses/create ...................................................................................... courses.create › CourseController@create
  GET|HEAD        courses/{course} ........................................................................................ courses.show › CourseController@show
  PUT|PATCH       courses/{course} .................................................................................... courses.update › CourseController@update
  DELETE          courses/{course} .................................................................................. courses.destroy › CourseController@destroy
  GET|HEAD        courses/{course}/edit ................................................................................... courses.edit › CourseController@edit
  GET|HEAD        dashboard .......................................................................................................................... dashboard
  GET|HEAD        tentang .................................................................................................................... routes/web.php:15
  GET|HEAD        users ..................................................................................................... users.index › UserController@index
  POST            users ..................................................................................................... users.store › UserController@store
  GET|HEAD        users/create ............................................................................................ users.create › UserController@create
  GET|HEAD        users/{user} ................................................................................................ users.show › UserController@show
  PUT|PATCH       users/{user} ............................................................................................ users.update › UserController@update
  DELETE          users/{user} .......................................................................................... users.destroy › UserController@destroy
  GET|HEAD        users/{user}/edit ........................................................................................... users.edit › UserController@edit

                                                                                                                                             Showing [17] routes
```

route yang menerima parameter model ( {coruse}, {user} ) ada **8**, di mana masing masing fitur mata kuliah dan fitur pengguna mempunyai **4 route** yang menerima parameter model. Untuk saat ini, role (admin, dosen, mahasiswa) belum memiliki fungsinya mereka sendiri, jadi tidak ada pembagian boleh dan tidak boleh diantara role tersebut.

> **IDOR** (_Insecure Direct Object Reference_) adalah celah keamanan dalam aplikasi web ketika sistem menyediakan akses langsung ke data atau file internal (seperti ID pengguna atau nomor file) tanpa memeriksa izin atau hak akses pengguna. Cara kerjanya: Pengguna biasa mengubah nilai ID di alamat URL (misalnya dari id=101bejadik id=102). Jika sistem rentan, mereka dapat melihat data orang lain secara ilegal.

**Titik Rawan IDOR**
| # | Titik Rawan |
|---|-------------|
| 1 | `courses/{course} .... courses.show › CourseController@show` |
| 2 | `courses/{course} .... courses.update › CourseController@update` |
| 3 | `courses/{course} .... courses.destroy › CourseController@destroy` |
| 4 | `courses/{course}/edit .... courses.edit › CourseController@edit` |
| 5 | `users/{user} .... users.show › UserController@show` |
| 6 | `users/{user} .... users.update › UserController@update` |
| 7 | `users/{user} .... users.destroy › UserController@destroy` |
| 8 | `users/{user}/edit .... users.edit › UserController@edit` |

Route tersebut sangat rawan karena route tersebut menerima parameter model yang punya celah keamanan membuat pengguna bisa mengubah nilai parameter langsung dari alamat URL.

---
### BREAK
| # | Yang dirusak | Yang dipelajari |
|---|--------------|-----------------|
| 1 | Login sebagai mahasiswa A. Buka submission milik mahasiswa B dengan mengubah angka di URL. |  |
| 2 | Buka /courses/1/assignments/99 di mana tugas 99 milik mata kuliah lain. |  |
| 3 | Aktifkan Route::scopeBindings(), ulangi nomor 2. |  |
| 4 | Daftarkan middleware di app/Http/Kernel.php seperti tutorial lama. |  |
| 5 | Pasang role:admin pada grup, lalu akses sebagai dosen. |  |
| 6 | Sebagai dosen A, edit mata kuliah milik dosen B (keduanya lolos role:dosen) |  |