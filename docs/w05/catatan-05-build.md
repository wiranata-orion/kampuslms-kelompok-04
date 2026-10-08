Struktur lengkap routes/web.php memakai route group.

Kondisi saat ini:

1. CourseController dan UserController sudah lengkap (index, create, store, show, edit, update, destroy) — cocok jadi kandidat Route::resource().
2. Route courses.* dan users.* saat ini flat, tidak dikelompokkan per peran — semua action (termasuk create/store/edit/destroy) diakses lewat /courses/... tanpa pembeda admin/dosen/mahasiswa.
3. Belum ada middleware auth maupun role terdaftar di bootstrap/app.php (withMiddleware masih kosong), dan belum ada sistem login sama sekali (tidak ada Breeze/Fortify, tidak ada LoginController). Ini gap yang harus diselesaikan.
4. Belum ada controller untuk Material, Assignment, Submission, Grade, Enrollment, Notification — rancangan route-nya pakai nama controller standar aja, karena memang gk ada ketentuan khusus mengenai itu.
5. Ada tentang.blade.php yang masih nyantol di routes/web.php (Route::get('/tentang', ...)) padahal overview.md proyek bilang file ini akan dihapus — saya tandai sebagai cleanup terpisah, bukan bagian tabel ini.

URI adalah Uniform Resource Identifier, pengidentifikasi sumber daya secara umum, misalnya `/courses/ProWeb/Week-04-hingga-07`. URL itu jenis URI yang nunjukkin lokasi dan cara ngakses sumer daya tersebut.

---
### A. Grup Auth (prasyarat untuk semua grup)
Middleware: `guest` untuk form login, `auth` untuk form logout
| Method | URL | Nama | Controller@Method | Peran | IDOR gk? |
| ------ | --- | ---- | ----------------- | ----- | -------- |
| GET | /login | login | AuthController@create | Tamu |	Tidak |
| POST | /login | login.store | AuthController@store | Tamu | Tidak |
| POST | /logout | logout | AuthController@destroy | Semua (login) | Tidak |

### B. Grup Umum
Middleware: `auth`
| Method | URL | Nama | Controller@Method | Peran | IDOR gk? |
| ------ | --- | ---- | ----------------- | ----- | -------- |
| GET | / | (redirect closure) | — | Semua | Tidak |
| GET | /dashboard | dashboard | DashboardController@index | Semua | Tidak |
| GET | /courses | courses.index | CourseController@index	Semua | Tidak |
| GET | /courses/{course} | courses.show | CourseController@show | Semua | Ya |
| GET | /notifications | notifications.index  |NotificationController@index | Semua	| Tidak |
| PATCH | /notifications/{notification}/read | notifications.read | NotificationController@markRead	| Semua	| Ya |
| PATCH | /notifications/read-all | notifications.readAll |NotificationController@markAllRead | Semua | Tidak |

### C. Grup Admin
Prefix: `admin`
Nama Prefix: `admin.`
Middleware: `auth`, `role:admin`
| Method | URL | Nama | Controller@Method | Peran | IDOR gk? |
| ------ | --- | ---- | ----------------- | ----- | -------- |
| GET | /admin/users | admin.users.index | 	UserController@index | Admin | Tidak |
| GET | /admin/users/create	| admin.users.create | UserController@create | Admin | Tidak |
| POST |/admin/users | admin.users.store | UserController@store | Admin | Tidak |
| GET	| /admin/users/{user} | admin.users.show | UserController@show	| Admin | Ya |
| GET	| /admin/users/{user}/edit	| admin.users.edit | UserController@edit	| Admin	| Ya |
| PUT/PATCH	| /admin/users/{user}	| admin.users.update	| UserController@update	| Admin	| Ya |
| DELETE	| /admin/users/{user}	| admin.users.destroy	| UserController@destroy	| Admin	| Ya |
| GET	| /admin/courses/create	| admin.courses.create	| CourseController@create	| Admin	| Tidak |
| POST	| /admin/courses	| admin.courses.store	| CourseController@store	| Admin	| Tidak |
| GET	| /admin/courses/{course}/edit	| admin.courses.edit | CourseController@edit	| Admin	| Ya |
| PUT/PATCH	| /admin/courses/{course}	| admin.courses.update	| CourseController@update	| Admin	| Ya |
| DELETE	| /admin/courses/{course}	| admin.courses.destroy	| CourseController@destroy	| Admin	| Ya |
| GET	| /admin/courses/{course}/enrollments	| admin.courses.enrollments.index	| EnrollmentController@index	| Admin	| Ya (course) |
| GET	| /admin/courses/{course}/enrollments/create	| admin.courses.enrollments.create	| EnrollmentController@create | Admin	| Ya (course) |
| POST	| /admin/courses/{course}/enrollments	| admin.courses.enrollments.store	| EnrollmentController@store	| Admin	| Ya (course) |
| DELETE	| /admin/enrollments/{enrollment}	| admin.enrollments.destroy	| EnrollmentController@destroy	| Admin | Ya |

### D. Grup Dosen
Prefix: `dosen`
Name Prefix: `dosen.`
Middleware: `auth`, `role:dosen` <-- nanti udah sama aturan kepemilikian tiap role nya
| Method | URL | Nama | Controller@Method | Peran | IDOR gk? |
| ------ | --- | ---- | ----------------- | ----- | -------- |
| GET        | /dosen/courses                                | dosen.courses.index                | CourseController@myCourses     | Dosen | Tidak          |
| GET        | /dosen/courses/{course}/materials             | dosen.courses.materials.index      | MaterialController@index       | Dosen | Ya (course)    |
| GET        | /dosen/courses/{course}/materials/create      | dosen.courses.materials.create     | MaterialController@create      | Dosen | Ya (course)    |
| POST       | /dosen/courses/{course}/materials             | dosen.courses.materials.store      | MaterialController@store       | Dosen | Ya (course)    |
| GET        | /dosen/materials/{material}                   | dosen.materials.show               | MaterialController@show        | Dosen | Ya             |
| GET        | /dosen/materials/{material}/edit              | dosen.materials.edit               | MaterialController@edit        | Dosen | Ya             |
| PUT/PATCH  | /dosen/materials/{material}                   | dosen.materials.update             | MaterialController@update      | Dosen | Ya             |
| DELETE     | /dosen/materials/{material}                   | dosen.materials.destroy            | MaterialController@destroy     | Dosen | Ya             |
| GET        | /dosen/courses/{course}/assignments           | dosen.courses.assignments.index    | AssignmentController@index     | Dosen | Ya (course)    |
| GET        | /dosen/courses/{course}/assignments/create    | dosen.courses.assignments.create   | AssignmentController@create    | Dosen | Ya (course)    |
| POST       | /dosen/courses/{course}/assignments           | dosen.courses.assignments.store    | AssignmentController@store     | Dosen | Ya (course)    |
| GET        | /dosen/assignments/{assignment}               | dosen.assignments.show             | AssignmentController@show      | Dosen | Ya             |
| GET        | /dosen/assignments/{assignment}/edit          | dosen.assignments.edit             | AssignmentController@edit      | Dosen | Ya             |
| PUT/PATCH  | /dosen/assignments/{assignment}               | dosen.assignments.update           | AssignmentController@update    | Dosen | Ya             |
| DELETE     | /dosen/assignments/{assignment}               | dosen.assignments.destroy          | AssignmentController@destroy   | Dosen | Ya             |
| GET        | /dosen/assignments/{assignment}/submissions   | dosen.assignments.submissions.index| SubmissionController@indexForDosen | Dosen | Ya (assignment) |
| GET        | /dosen/submissions/{submission}               | dosen.submissions.show             | SubmissionController@showForDosen | Dosen | Ya             |
| GET        | /dosen/submissions/{submission}/grade/create  | dosen.submissions.grade.create     | GradeController@create         | Dosen | Ya (submission)|
| POST       | /dosen/submissions/{submission}/grade         | dosen.submissions.grade.store      | GradeController@store          | Dosen | Ya (submission)|
| GET        | /dosen/grade/{grade}/edit                     | dosen.grade.edit                   | GradeController@edit           | Dosen | Ya             |
| PUT/PATCH  | /dosen/grade/{grade}                          | dosen.grade.update                 | GradeController@update         | Dosen | Ya             |

Karena ada risiko IDOR, maka setiap route wajib dicek `course->lecturer_id === auth()->id()`

### E. Grup Mahasiswa
Prefix: `mahasiswa`
Name Prefix: `mahasiswa.`
Middleware: `auth` `role:dosen` <--- sama kayak dosen
| Method | URL | Nama | Controller@Method | Peran | IDOR gk? |
| ------ | --- | ---- | ----------------- | ----- | -------- |
| GET        | /mahasiswa/courses                                       | mahasiswa.courses.index                 | CourseController@myCourses   | Mahasiswa | Tidak          |
| GET        | /mahasiswa/assignments/{assignment}/submissions/create   | mahasiswa.assignments.submissions.create| SubmissionController@create  | Mahasiswa | Ya (assignment)|
| POST       | /mahasiswa/assignments/{assignment}/submissions          | mahasiswa.assignments.submissions.store | SubmissionController@store   | Mahasiswa | Ya (assignment)|
| GET        | /mahasiswa/submissions/{submission}                      | mahasiswa.submissions.show              | SubmissionController@show    | Mahasiswa | Ya             |
| GET        | /mahasiswa/submissions/{submission}/edit                 | mahasiswa.submissions.edit              | SubmissionController@edit    | Mahasiswa | Ya             |
| PUT/PATCH  | /mahasiswa/submissions/{submission}                      | mahasiswa.submissions.update            | SubmissionController@update  | Mahasiswa | Ya             |
| GET        | /mahasiswa/submissions/{submission}/grade                | mahasiswa.submissions.grade.show        | GradeController@showForStudent | Mahasiswa | Ya             |

Submissions di mahasiswa itu gk punya destroy, karena kan kalau tugasnya udah dikirim gk boleh dihapus lagi. Kecuali masih tahap diedit.
Terus karena ada risiko IDOR, maka setiap route wajib dicek `submission->user_id === auth()->id()`

File yang kita perlu buat/edit:
1. app/Http/Middleware/EnsureUserHasRole.php 
2. bootstrap/app.php (edit)
3. app/Policies/CoursePolicy.php (dibuat di minggu ke 7)
4. app/Policies/MaterialPolicy.php (dibuat di minggu ke 7)
5. app/Policies/AssignmentPolicy.php (dibuat di minggu ke 7)
6. app/Policies/SubmissionPolicy.php (dibuat di minggu ke 7)
7. app/Policies/GradePolicy.php (dibuat di minggu ke 7)
8. app/Policies/NotificationPolicy.php (dibuat di minggu ke 7)
9. app/Providers/AppServiceProvider.php (edit) atau bikin AuthServiceProvider
10. app/Http/Controllers/AuthController.php
11. app/Http/Controllers/DashboardController.php
12. app/Http/Controllers/CourseController.php
13. app/Http/Controllers/UserController.php
14. app/Http/Controllers/MaterialController.php
15. app/Http/Controllers/AssignmentController.php
16. app/Http/Controllers/SubmissionController.php
17. app/Http/Controllers/GradeController.php
18. app/Http/Controllers/Admin/EnrollmentController.php
19. app/Http/Controllers/NotificationController.php
20. app/Http/Requests/StoreCourseRequest.php
21. app/Http/Requests/UpdateCourseRequest.php
22. app/Http/Requests/LoginRequest.php
23. app/Http/Requests/StoreUserRequest.php
24. app/Http/Requests/UpdateUserRequest.php
25. app/Http/Requests/StoreMaterialRequest.php
26. app/Http/Requests/UpdateMaterialRequest.php
27. app/Http/Requests/StoreAssignmentRequest.php
28. app/Http/Requests/UpdateAssignmentRequest.php
29. app/Http/Requests/StoreSubmissionRequest.php
30. app/Http/Requests/UpdateSubmissionRequest.php
31. app/Http/Requests/StoreGradeRequest.php
32. app/Http/Requests/UpdateGradeRequest.php
33. app/Http/Requests/StoreEnrollmentRequest.php	
34. app/Models/Enrollment.php
35. app/Models/Notification.php (dibuat di minggu ke 9)
36. resources/views/auth/login.blade.php
37. resources/views/courses/index.blade.php
38. resources/views/courses/show.blade.php
39. resources/views/courses/create.blade.php, edit.blade.php
40. resources/views/users/*.blade.php (4 file)
41. resources/views/dashboard.blade.php (atau tetap pakai about.blade.php)
42. resources/views/materials/index.blade.php, create.blade.php, edit.blade.php, show.blade.php
43. resources/views/assignments/index.blade.php, create.blade.php, edit.blade.php, show.blade.php
44. resources/views/submissions/create.blade.php, edit.blade.php, show.blade.php, index.blade.php (untuk dosen)
45. resources/views/grades/create.blade.php, edit.blade.php, show.blade.php
46. resources/views/enrollments/index.blade.php, create.blade.php
47. resources/views/notifications/index.blade.php
48. resources/views/components/layout.blade.php
49. resources/views/errors/403.blade.php, 500.blade.php (dibuat di minggu ke 7)
50. resources/views/tentang.blade.php
51. routes/web.php