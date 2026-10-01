### READ — Peta route Anda sendiri (30 menit)

1. Jalankan `php artisan route:list --except-vendor`. Salin keluarannya ke catatan.
2. Tandai setiap route yang menerima parameter model (`{course}`, `{assignment}`, dst).
3. Untuk setiap route bertanda, jawab: **siapa saja yang seharusnya boleh mengaksesnya, dan apa yang saat ini mencegah orang lain?** Kemungkinan besar jawabannya "belum ada apa-apa" — itu wajar, dan itulah pekerjaan minggu ini dan minggu 7.
4. Buat tabel di `docs/minggu-05-<nama>.md` berjudul "Daftar Titik Rawan IDOR". Tabel ini akan Anda pakai lagi di minggu 7 dan saat interview.


### Jawaban
1. 
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
2. Dari 17 route tersebut, terdapat 8 route yang memiliki parameter model.
- Parameter `{course}`
```shell
GET|HEAD      courses/{course}
PUT|PATCH     courses/{course}
DELETE        courses/{course}
GET|HEAD      courses/{course}/edit
```
- Parameter `{user}`
```shell
GET|HEAD      users/{user}
PUT|PATCH     users/{user}
DELETE        users/{user}
GET|HEAD      users/{user}/edit
```
3. Siapa yang boleh mengakses dan apa yang mencegah orang lain?

- Route `courses/{course}`

| Route                        | Yang seharusnya boleh mengakses                                  | Apa yang saat ini mencegah orang lain?                                                               |
|------------------------------|------------------------------------------------------------------|------------------------------------------------------------------------------------------------------|
| `GET courses/{course}`       | User yang memiliki hak melihat course                            | Model binding hanya memastikan `course` tersebut ada; belum tentu mengecek hak akses                 |
| `PUT/PATCH courses/{course}` | User yang memiliki hak mengubah course, misalnya pengelola/dosen | Model binding hanya menemukan course berdasarkan ID; belum tentu memeriksa siapa yang boleh mengubah |
| `DELETE courses/{course}`    | User yang memiliki hak menghapus course                          | Model binding tidak otomatis memastikan user memiliki hak menghapus                                  |
| `GET courses/{course}/edit`  | User yang memiliki hak mengedit course                           | Model binding hanya memastikan course ditemukan; authorization perlu dibuat                          |

- Route `users/{user}`

| Route                    | Yang seharusnya boleh mengakses                            | Apa yang saat ini mencegah orang lain?                     |
| ------------------------ | ---------------------------------------------------------- | ---------------------------------------------------------- |
| `GET users/{user}`       | Admin atau user yang memang memiliki hak melihat data user | Model binding hanya memastikan user dengan ID tersebut ada |
| `PUT/PATCH users/{user}` | Admin atau user yang berhak mengubah data tersebut         | Model binding tidak otomatis mengecek hak edit             |
| `DELETE users/{user}`    | Admin atau pihak yang memiliki hak menghapus user          | Model binding tidak otomatis mengecek hak delete           |
| `GET users/{user}/edit`  | Admin atau user yang berhak mengedit data tersebut         | Model binding hanya mengambil objek user                   |

4. Daftar Titik Rawan IDOR

Berdasarkan hasil `php artisan route:list --except-vendor`, terdapat 8 route yang menerima parameter model, yaitu parameter `{course}` dan `{user}`.

| No. | Method    | Route                   | Parameter Model | Siapa yang Seharusnya Boleh Mengakses?                     | Perlindungan Saat Ini                                              | Potensi IDOR |
| --: | --------- | ----------------------- | --------------- | ---------------------------------------------------------- | ------------------------------------------------------------------ | ------------ |
|   1 | GET/HEAD  | `courses/{course}`      | `{course}`      | User yang memiliki hak untuk melihat course tersebut       | Model binding hanya memastikan course dengan ID tersebut ditemukan | Ya           |
|   2 | PUT/PATCH | `courses/{course}`      | `{course}`      | User yang memiliki hak untuk mengubah course               | Model binding tidak otomatis memeriksa hak mengubah                | Ya           |
|   3 | DELETE    | `courses/{course}`      | `{course}`      | User yang memiliki hak untuk menghapus course              | Model binding tidak otomatis memeriksa hak menghapus               | Ya           |
|   4 | GET/HEAD  | `courses/{course}/edit` | `{course}`      | User yang memiliki hak untuk mengedit course               | Model binding tidak otomatis memeriksa hak edit                    | Ya           |
|   5 | GET/HEAD  | `users/{user}`          | `{user}`        | Admin atau user yang memiliki hak untuk melihat data user  | Model binding hanya memastikan user dengan ID tersebut ditemukan   | Ya           |
|   6 | PUT/PATCH | `users/{user}`          | `{user}`        | Admin atau user yang memiliki hak untuk mengubah data user | Model binding tidak otomatis memeriksa hak mengubah                | Ya           |
|   7 | DELETE    | `users/{user}`          | `{user}`        | Admin atau pihak yang memiliki hak untuk menghapus user    | Model binding tidak otomatis memeriksa hak menghapus               | Ya           |
|   8 | GET/HEAD  | `users/{user}/edit`     | `{user}`        | Admin atau user yang memiliki hak untuk mengedit data user | Model binding tidak otomatis memeriksa hak edit                    | Ya           |
