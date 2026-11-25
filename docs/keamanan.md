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
| Lihat assignment | Semua | Tugas course yang diampu | Tugas `published` pada course `active` yang diikuti |
| Buat/ubah assignment | Ya | Hanya pada course yang diampu | Tidak |
| Hapus assignment | Jika belum memiliki nilai | Jika course diampu dan belum memiliki nilai | Tidak |
| Lihat daftar submission per tugas | Ya | Jika course diampu | Tidak |
| Lihat/unduh submission | Ya | Jika course diampu | Submission milik sendiri |
| Membuat submission | Tidak | Tidak | Peserta terdaftar, tugas `published`, course `active`, dan belum pernah mengumpulkan |
| Mengubah submission | Tidak | Tidak | Iya; Jika belum dinilai |
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

| Endpoint / target ID | Risiko lintas pengguna/peran | Policy atau query yang menutupnya | Test |
|---|---|---|---|
| `GET /courses`, `GET /my/courses` | Mengirim `scope=all` untuk membaca katalog course lain | `CoursePolicy::viewAny`; `CourseController::index` membatasi query ke course admin, `taughtCourses`, atau `courses` aktif mahasiswa | `test_course_index_is_scoped_in_the_query_even_with_scope_all`; `test_course_collections_are_scoped_and_include_counts_and_lecturer` |
| `GET /courses/{course}` | Mengganti ID untuk membaca course lain | `CoursePolicy::view` memeriksa admin, `lecturer_id`, atau enrollment `exists()` serta status course mahasiswa | `test_student_cannot_read_nonmember_content_archived_courses_or_draft_assignments`; `test_course_collections_are_scoped_and_include_counts_and_lecturer` |
| `POST/PUT/DELETE /courses[/{course}]` | Membuat atau mengubah course / menghapus course berisi data | `CoursePolicy::create/update/delete`; delete memakai `students()->exists()`, `materials()->exists()`, dan `assignments()->exists()` | `test_admin_successfully_creates_course`; `test_admin_only_endpoints_reject_lecturers_and_students`; `test_admin_deletion_is_blocked_when_course_or_assignment_has_restricted_data` |
| `GET /courses/{course}/materials` | Mengganti ID course untuk membaca materi course lain | `MaterialPolicy::viewAny` terhadap course induk | `test_student_cannot_read_nonmember_content_archived_courses_or_draft_assignments` |
| `GET /materials/{material}` dan `/download` | Membaca metadata atau mengunduh materi privat dengan IDOR | `MaterialPolicy::view` melalui `Material::course`; mahasiswa harus terdaftar di course aktif | `test_student_cannot_read_nonmember_content_archived_courses_or_draft_assignments`; `test_material_file_download_is_authenticated_and_course_scoped` |
| `POST /courses/{course}/materials` | Mengunggah materi ke course yang bukan milik dosen | `MaterialPolicy::create` di `StoreMaterialRequest`; admin atau dosen pengampu | `test_other_lecturer_is_forbidden_from_uploading_material`; `test_course_lecturer_successfully_uploads_material` |
| `PUT/DELETE /materials/{material}` | Mengubah/menghapus materi course lain | `MaterialPolicy::update/delete` memeriksa pemilik course (admin atau dosen pengampu) | `test_material_crud_is_course_scoped_and_stores_file_metadata_without_uploading` |
| `GET /courses/{course}/assignments` | Membaca tugas dari course lain atau meminta draft sebagai mahasiswa | `AssignmentPolicy::viewAny`; query course induk dan filter `status=published` mahasiswa | `test_student_cannot_read_nonmember_content_archived_courses_or_draft_assignments` |
| `GET /assignments/{assignment}` | Mengganti ID tugas, termasuk membuka draft | `AssignmentPolicy::view`; mahasiswa hanya tugas published pada course aktif yang diikuti | `test_student_cannot_read_nonmember_content_archived_courses_or_draft_assignments` |
| `POST /assignments` | Memalsukan `course_id` untuk membuat tugas di course dosen lain | `AssignmentPolicy::create` menerima course hasil lookup server | `test_assignment_crud_is_course_scoped_and_delete_only_checks_for_grades` |
| `PUT/PATCH/DELETE /assignments/{assignment}` | Mengubah/menghapus tugas milik pengampu lain | `AssignmentPolicy::update/delete`; delete juga memeriksa `grades()->exists()` | `test_assignment_crud_is_course_scoped_and_delete_only_checks_for_grades`; `test_admin_deletion_is_blocked_when_course_or_assignment_has_restricted_data` |
| `GET /assignments/{assignment}/submissions` | Membaca daftar jawaban course lain | `SubmissionPolicy::viewAny` memeriksa admin atau dosen pengampu | `test_submission_cross_role_idor_draft_duplicate_and_no_revision_scenarios` |
| `POST /assignments/{assignment}/submissions` | Mengirim jawaban pada tugas orang lain, draft, atau mengirim ulang | `SubmissionPolicy::create` di `StoreSubmissionRequest`; peran, enrollment `exists()`, status, dan course aktif. Pengumpulan ulang menggunakan `updateOrCreate` menggantikan berkas lama | `test_submission_cross_role_idor_draft_duplicate_and_no_revision_scenarios`; `test_submission_replacement_requires_an_ungraded_enrolled_owner` |
| `GET /my/submissions` | Membaca submission pengguna lain lewat daftar | `SubmissionPolicy::viewAny` dan query relasi `$user->submissions()` | `test_my_submissions_are_scoped_and_permissions_use_batched_relations` |
| `GET /submissions/{submission}` dan `/download` | Mengganti ID untuk membaca/mengunduh jawaban mahasiswa lain | `SubmissionPolicy::view`: admin, dosen pengampu, atau pemilik submission. File disimpan di disk privat `local` | `test_submission_cross_role_idor_draft_duplicate_and_no_revision_scenarios`; `test_download_endpoint_authorizes_via_policy_and_serves_from_private_disk` |
| `PUT /submissions/{submission}` | Mengubah jawaban memakai ID submission | Revisi pengumpulan tugas dilakukan lewat endpoint `POST /assignments/{assignment}/submissions` (`updateOrCreate`) | `test_unavailable_submission_and_grade_mutation_routes_stay_unregistered` |
| `DELETE /submissions/{submission}` (belum ada route) | Menghapus jawaban milik pengguna/course lain | `SubmissionPolicy::delete`: admin atau dosen pengampu | `test_unavailable_submission_and_grade_mutation_routes_stay_unregistered` |
| `PUT /submissions/{submission}/grade` | Memberi/mengubah nilai milik course lain | `GradePolicy::create/update` menuntut dosen pengampu dari submission | `test_other_lecturer_is_forbidden_from_grading_submission`; `test_course_lecturer_successfully_grades_submission` |
| `GET /grades/{grade}`, `GET /submissions/{submission}/grade` | Membaca nilai pengguna lain atau nilai yang belum diumumkan | `GradePolicy::view`; mahasiswa hanya nilai submission sendiri dengan `published_at` terisi | `test_deadline_validation_is_422_and_grade_visibility_requires_publication`; `test_grade_summaries_are_authorized_and_student_sees_only_own_published_grades` |
| `POST /grades/{grade}/publish` | Mempublikasikan nilai course lain | `GradePolicy::publish` hanya dosen pengampu | `test_deadline_validation_is_422_and_grade_visibility_requires_publication`; `test_other_lecturer_is_forbidden_from_grading_submission` |
| `DELETE /grades/{grade}` (belum ada route) | Menghapus nilai milik course lain | `GradePolicy::delete`: admin atau dosen pengampu | `test_unavailable_submission_and_grade_mutation_routes_stay_unregistered` |
| `GET /courses/{course}/students` dan enrollment candidates | Melihat/mengelola roster course lain | `CoursePolicy::manageEnrollment`: admin atau dosen pengampu; candidates memakai `whereDoesntHave` (bukan memuat seluruh ID enrollment) | `test_enrollment_management_is_limited_to_admin_and_the_course_lecturer`; `test_student_cannot_read_or_change_course_enrollments` |
| `POST/DELETE /courses/{course}/enrollments[/...]` | Menambah/mengeluarkan mahasiswa pada course lain | `CoursePolicy::manageEnrollment`; target harus ber-role mahasiswa | `test_enrollment_management_is_limited_to_admin_and_the_course_lecturer`; `test_student_cannot_read_or_change_course_enrollments`; `test_admin_user_course_and_enrollment_crud_are_available_through_api` |
| `GET/PUT /users/{user}`, `GET/PATCH /me` | Membaca/mengubah profil akun lain atau mengubah role sendiri | `UserPolicy::view/update`; admin atau pemilik akun, pemilik hanya dapat mengubah nama/kata sandi dan tidak dapat mengubah role | `test_user_policy_allows_self_profile_but_protects_roles_and_other_accounts` |
| `GET/POST /users` | Mengakses daftar akun atau membuat akun melalui role selain admin | `UserPolicy::viewAny/create` hanya mengizinkan admin | `test_admin_only_endpoints_reject_lecturers_and_students`; `test_admin_user_course_and_enrollment_crud_are_available_through_api` |
| `DELETE /users/{user}` | Menghapus akun sendiri atau dosen yang masih mengampu course | `UserPolicy::delete`; diri sendiri ditolak dan dosen hanya dapat dihapus jika `taughtCourses()->exists()` bernilai false | `test_user_policy_allows_self_profile_but_protects_roles_and_other_accounts` |

## Skenario uji submission dan berkas (Minggu 9)

`tests/Feature/AuthorizationPolicyTest.php`, `tests/Feature/SubmissionUploadTest.php`, dan `scripts/test-authz.sh`
memverifikasi skenario berikut:

- Mahasiswa peserta dapat mengumpulkan submission ke tugas published pada course aktif.
- Mahasiswa yang bukan peserta, dosen, dan admin ditolak untuk submission mahasiswa.
- Mahasiswa dapat mengumpulkan ulang (revisi): baris database lama ditimpa lewat `updateOrCreate` dan berkas fisik lama dihapus dari disk privat.
- Validasi berkas: wajib ada, `mimes:pdf,doc,docx`, maksimal 5 MB (`5120 KB`).
- Deadline yang terlewati ketika `allow_late=false` menghasilkan `422`.
- `is_late` dihitung di server berdasarkan `due_at`.
- Pemilik submission, dosen pengampu, dan admin dapat melihat dan mengunduh submission; pihak lain menghasilkan `403`.
- Berkas fisik dihapus secara otomatis saat record dihapus via Eloquent deleting event.
- Dosen lain tidak dapat memberi atau mempublikasikan nilai; mahasiswa hanya dapat melihat nilainya sendiri setelah dosen mempublikasikannya.

Jalankan tes terfokus lokal dengan:

```sh
./scripts/test-authz.sh --local
```

Skrip yang sama juga menyediakan smoke test read-only terhadap production.
Skrip otomatis login memakai akun demo dari seeder dengan password `password`,
menemukan ID course dan draft assignment yang diperlukan, serta tidak memiliki
status `SKIP`. Jika request atau data yang diperlukan tidak tersedia, pengujian
langsung gagal.

```sh
AUTHZ_BASE_URL=https://lacikampus-production.up.railway.app \
  ./scripts/test-authz.sh
```

Environment variable `AUTHZ_*` tetap dapat dipakai untuk mengganti password,
token, atau ID saat diperlukan. Skrip tidak membuat, mengubah, menghapus, atau
mengunggah data. Empat security header (`Strict-Transport-Security`,
`X-Content-Type-Options`, `X-Frame-Options`, dan `Content-Security-Policy`)
merupakan check wajib.
