# Otorisasi dan pencegahan IDOR

Laravel 12 menemukan policy secara otomatis berdasarkan pasangan
`App\Models\Foo` dan `App\Policies\FooPolicy`. Policy tidak didaftarkan lagi
di `AuthServiceProvider`.

## Aturan yang diterapkan

| Aksi | Admin | Dosen | Mahasiswa |
|---|---|---|---|
| Lihat course | Semua | Course yang diampu | Course `active` yang diikuti |
| Buat/ubah course | Ya | Tidak | Tidak |
| Hapus course | Hanya jika tidak memiliki enrollment, materi, atau tugas | Tidak | Tidak |
| Lihat materi | Semua | Materi course yang diampu | Materi course `active` yang diikuti |
| CRUD materi | Ya | Hanya pada course yang diampu | Tidak |
| Lihat tugas | Semua | Tugas course yang diampu | Tugas `published` pada course `active` yang diikuti |
| Buat/ubah tugas | Ya | Hanya pada course yang diampu | Tidak |
| Hapus tugas | Jika belum memiliki nilai | Jika course diampu dan belum memiliki nilai | Tidak |
| Lihat daftar submission per tugas | Ya | Jika course diampu | Tidak |
| Lihat/unduh submission | Ya | Jika course diampu | Submission milik sendiri |
| Membuat submission | Tidak | Tidak | Peserta terdaftar, tugas `published`, course `active`, dan belum pernah mengumpulkan |
| Mengubah submission | Tidak | Tidak | Tidak; revisi tidak diperbolehkan |
| Menghapus submission | Ya | Jika course diampu | Tidak |
| Melihat nilai | Ya | Jika course diampu | Nilai sendiri setelah dipublikasikan |
| Memberi/mengubah/mempublikasikan nilai | Tidak | Jika course diampu | Tidak |
| Menghapus nilai | Ya | Jika course diampu | Tidak |
| Mengelola enrollment | Semua course | Course yang diampu | Tidak |
| Mengelola akun | CRUD admin; tidak dapat menghapus diri, atau dosen yang masih mengampu course | Mengubah profil sendiri | Mengubah profil sendiri |

Deadline submission tetap merupakan validasi Form Request (`422`), bukan aturan
otorisasi (`403`). Pilihan `scope=all` tidak memperluas katalog dosen atau
mahasiswa: daftar course tetap dibatasi oleh query berdasarkan role. Penghapusan
course ditolak selama masih memiliki enrollment, materi, atau tugas; penghapusan
tugas ditolak hanya jika sudah memiliki nilai. Submission yang sudah dibuat
tetap tersimpan saat mahasiswa dikeluarkan dari course.

## Pemanggilan policy

Di controller, otorisasi dijalankan sebelum operasi terhadap model:

```php
use Illuminate\Support\Facades\Gate;

$course = Course::findOrFail($id);
Gate::authorize('view', $course);
```

Form Request dapat memeriksa policy terhadap model yang terikat ke route. Contoh
ini digunakan pada pembuatan submission:

```php
public function authorize(): bool
{
    $assignment = $this->route('assignment');

    return $assignment instanceof Assignment
        && Gate::allows('create', [Submission::class, $assignment]);
}
```

Pada Blade server-rendered, `@can` dapat menyembunyikan kontrol yang tidak boleh
dipakai:

```blade
@can('create', \App\Models\Course::class)
    <button type="button">Tambah mata kuliah</button>
@endcan
```

Blade hanya membatasi tampilan; ia tidak mencegah request HTTP langsung. Aplikasi
ini merender shell SPA, sehingga Vue sebaiknya memakai flag `permissions` pada
resource detail API (misalnya `data.permissions.update`) untuk menampilkan
kontrol. Flag hanya petunjuk UI: seluruh endpoint tetap memanggil policy di
server. Jangan menjadikan nilai yang dikirim browser sebagai bukti otorisasi.

## Titik rawan IDOR

Setiap endpoint di tabel tetap memerlukan autentikasi Sanctum. Policy/query
menentukan akses setelah record ditemukan; ID yang tidak ada menghasilkan `404`,
sedangkan record yang ada tetapi bukan hak pemanggil menghasilkan `403`.

| Endpoint / target ID | Risiko lintas pengguna/peran | Policy atau query yang menutupnya |
|---|---|---|
| `GET /courses`, `GET /my/courses` | Mengirim `scope=all` untuk membaca katalog course lain | `CoursePolicy::viewAny`; `CourseApiController::index` membatasi query ke course admin, `taughtCourses`, atau `courses` aktif mahasiswa |
| `GET /courses/{course}` | Mengganti ID untuk membaca course lain | `CoursePolicy::view` memeriksa admin, `lecturer_id`, atau enrollment `exists()` serta status course mahasiswa |
| `POST/PUT/DELETE /courses[/{course}]` | Membuat atau mengubah course / menghapus course berisi data | `CoursePolicy::create/update/delete`; delete memakai `students()->exists()`, `materials()->exists()`, dan `assignments()->exists()` |
| `GET /courses/{course}/materials` | Mengganti ID course untuk membaca materi course lain | `MaterialPolicy::viewAny` terhadap course induk |
| `GET /materials/{material}` dan `/download` | Membaca metadata atau mengunduh materi privat dengan IDOR | `MaterialPolicy::view` melalui `Material::course`; mahasiswa harus terdaftar di course aktif |
| `POST /courses/{course}/materials` | Mengunggah materi ke course yang bukan milik dosen | `MaterialPolicy::create` di `StoreMaterialRequest`; admin atau dosen pengampu |
| `PUT/DELETE /materials/{material}` | Mengubah/menghapus materi course lain | `MaterialPolicy::update/delete` memeriksa pemilik course (admin atau dosen pengampu) |
| `GET /courses/{course}/assignments` | Membaca tugas dari course lain atau meminta draft sebagai mahasiswa | `AssignmentPolicy::viewAny`; query course induk dan filter `status=published` mahasiswa |
| `GET /assignments/{assignment}` | Mengganti ID tugas, termasuk membuka draft | `AssignmentPolicy::view`; mahasiswa hanya tugas published pada course aktif yang diikuti |
| `POST /assignments` | Memalsukan `course_id` untuk membuat tugas di course dosen lain | `AssignmentPolicy::create` menerima course hasil lookup server |
| `PUT/PATCH/DELETE /assignments/{assignment}` | Mengubah/menghapus tugas milik pengampu lain | `AssignmentPolicy::update/delete`; delete juga memeriksa `grades()->exists()` |
| `GET /assignments/{assignment}/submissions` | Membaca daftar jawaban course lain | `SubmissionPolicy::viewAny` memeriksa admin atau dosen pengampu |
| `POST /assignments/{assignment}/submissions` | Mengirim jawaban pada tugas orang lain, draft, atau mengirim ulang | `SubmissionPolicy::create` di `StoreSubmissionRequest`; peran, enrollment `exists()`, status, course aktif, dan `submissions()->exists()` |
| `GET /my/submissions` | Membaca submission pengguna lain lewat daftar | `SubmissionPolicy::viewAny` dan query relasi `$user->submissions()` |
| `GET /submissions/{submission}` dan `/download` | Mengganti ID untuk membaca/mengunduh jawaban mahasiswa lain | `SubmissionPolicy::view`: admin, dosen pengampu, atau pemilik submission |
| `PUT /submissions/{submission}` | Mengubah jawaban memakai ID submission | `SubmissionPolicy::update` selalu menolak; pengumpulan ulang memang tidak diperbolehkan |
| `DELETE /submissions/{submission}` (belum ada route) | Menghapus jawaban milik pengguna/course lain | `SubmissionPolicy::delete`: admin atau dosen pengampu |
| `PUT /submissions/{submission}/grade` | Memberi/mengubah nilai milik course lain | `GradePolicy::create/update` menuntut dosen pengampu dari submission |
| `GET /grades/{grade}`, `GET /submissions/{submission}/grade` | Membaca nilai pengguna lain atau nilai yang belum diumumkan | `GradePolicy::view`; mahasiswa hanya nilai submission sendiri dengan `published_at` terisi |
| `POST /grades/{grade}/publish` | Mempublikasikan nilai course lain | `GradePolicy::publish` hanya dosen pengampu |
| `DELETE /grades/{grade}` (belum ada route) | Menghapus nilai milik course lain | `GradePolicy::delete`: admin atau dosen pengampu |
| `GET /courses/{course}/students` dan enrollment candidates | Melihat/mengelola roster course lain | `CoursePolicy::manageEnrollment`: admin atau dosen pengampu; candidates memakai `whereDoesntHave` (bukan memuat seluruh ID enrollment) |
| `POST/DELETE /courses/{course}/enrollments[/...]` | Menambah/mengeluarkan mahasiswa pada course lain | `CoursePolicy::manageEnrollment`; target harus ber-role mahasiswa |
| `GET/PUT /users/{user}`, `GET/PATCH /me` | Membaca/mengubah profil akun lain atau mengubah role sendiri | `UserPolicy::view/update`; admin atau pemilik akun, pemilik hanya dapat mengubah nama/kata sandi dan tidak dapat mengubah role |
| `GET/POST /users` | Mengakses daftar akun atau membuat akun melalui role selain admin | `UserPolicy::viewAny/create` hanya mengizinkan admin |
| `DELETE /users/{user}` | Menghapus akun sendiri atau dosen yang masih mengampu course | `UserPolicy::delete`; diri sendiri ditolak dan dosen hanya dapat dihapus jika `taughtCourses()->exists()` bernilai false |

## Skenario uji submission (Tugas 7)

`tests/Feature/AuthorizationPolicyTest.php` dan `scripts/test-authz.sh`
memverifikasi skenario berikut:

- Mahasiswa peserta dapat mengumpulkan submission satu kali ke tugas published
  pada course aktif.
- Mahasiswa yang bukan peserta, dosen, dan admin ditolak; mahasiswa juga ditolak
  untuk tugas draft atau submission kedua.
- Deadline yang terlewati ketika `allow_late=false` menghasilkan `422`.
- Pemilik submission dapat melihat dan mengunduh submission sendiri, tetapi ID
  submission mahasiswa lain ditolak.
- Dosen pengampu dan admin dapat melihat/mengunduh submission; dosen lain tidak.
- Endpoint revisi submission ditolak untuk semua peran.
- Dosen lain tidak dapat memberi atau memublikasikan nilai; mahasiswa hanya
  dapat melihat nilainya sendiri setelah dosen memublikasikannya.

Jalankan tes terfokus dengan:

```sh
./scripts/test-authz.sh
```
