# API KampusLMS

Dokumentasi REST API versi 1. Semua endpoint memakai prefix `/api/v1` dan menerima JSON. Contoh memakai server lokal `http://127.0.0.1:8000`.

## Akun Demo dan Autentikasi

Endpoint selain `POST /auth/login` memerlukan token Sanctum di header `Authorization: Bearer <token>`.

| Peran | Email | Password default |
|---|---|---|
| Admin | `admin@kampuslms.test` | `1234` |
| Dosen | `dosen@kampuslms.test` | `1234` |
| Mahasiswa | `mahasiswa@kampuslms.test` | `1234` |

Akun tersebut dibuat oleh `DemoAccountSeeder`. Password default berlaku jika `.env` tidak menetapkan `DEMO_ACCOUNT_PASSWORD`; jika variabel itu ada, gunakan nilainya. Jalankan seeder sebelum memakai akun demo.

Contoh login dosen dan menyimpan token (membutuhkan `curl` dan `jq`):

```bash
API="http://127.0.0.1:8000/api/v1"
TOKEN_DOSEN=$(curl -sS -X POST "$API/auth/login" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"dosen@kampuslms.test","password":"1234","device_name":"docs"}' \
  | jq -r '.data.token')
```

Gunakan email `admin@kampuslms.test` atau `mahasiswa@kampuslms.test` untuk memperoleh token dengan role yang berbeda. Database seeder membuat ID resource secara dinamis dan memilih dosen pengampu secara acak. Ambil ID course dari daftar API; jangan menganggap suatu ID selalu dimiliki Dosen Demo.

## Format Umum

- Koleksi dipaginasi 15 item per halaman. Parameter query `page` opsional, default `1`.
- Respons koleksi berbentuk `data` dan `meta`; resource tunggal dibungkus dalam `data`.
- Role yang digunakan: `admin`, `dosen`, dan `mahasiswa`.
- Header request terautentikasi: `Accept: application/json` dan `Authorization: Bearer <token>`.

Contoh format error API:

```json
{"message":"Unauthenticated."}
```

```json
{"message":"Anda tidak memiliki akses ke sumber daya ini."}
```

```json
{
	"message": "Data yang diberikan tidak valid.",
	"errors": { "email": ["Email atau kata sandi salah."] }
}
```

## Autentikasi

### Login

**Method / URI:** `POST /api/v1/auth/login`  
**Peran:** Publik.  
**Parameter body:** `email` (wajib, email), `password` (wajib, string), `device_name` (opsional, string maks. 100 karakter).

```bash
curl -X POST "$API/auth/login" \
	-H "Accept: application/json" -H "Content-Type: application/json" \
	-d '{"email":"dosen@kampuslms.test","password":"1234","device_name":"docs"}'
```

**Sukses `200`:**

```json
{
	"data": {
		"token": "1|<sanctum-token>",
		"user": {
			"id": 2,
			"name": "Dosen Demo",
			"email": "dosen@kampuslms.test",
			"role": "dosen",
			"nim_nip": "198801012022011001",
			"created_at": "2026-10-02T10:00:00+00:00"
		}
	}
}
```

**Gagal `422` (kredensial salah):**

```json
{
	"message": "Data yang diberikan tidak valid.",
	"errors": { "email": ["Email atau kata sandi salah."] }
}
```

### Logout

**Method / URI:** `POST /api/v1/auth/logout`  
**Peran:** Semua role dengan token valid.  
**Parameter:** Tidak ada.

```bash
curl -X POST "$API/auth/logout" -H "Accept: application/json" \
	-H "Authorization: Bearer $TOKEN_DOSEN"
```

**Sukses `200`:**

```json
{"data":{"message":"Berhasil logout."}}
```

**Gagal `401`:** `{"message":"Unauthenticated."}`.

### Pengguna saat ini

**Method / URI:** `GET /api/v1/me`  
**Peran:** Semua role dengan token valid.  
**Parameter:** Tidak ada.

```bash
curl "$API/me" -H "Accept: application/json" \
	-H "Authorization: Bearer $TOKEN_DOSEN"
```

**Sukses `200`:**

```json
{
	"data": {
		"id": 2,
		"name": "Dosen Demo",
		"email": "dosen@kampuslms.test",
		"role": "dosen",
		"nim_nip": "198801012022011001",
		"created_at": "2026-10-02T10:00:00+00:00"
	}
}
```

**Gagal `401`:** `{"message":"Unauthenticated."}`.

## Mata Kuliah

### Daftar mata kuliah

**Method / URI:** `GET /api/v1/courses`  
**Peran:** Semua role. Admin melihat semua course, dosen melihat course yang diampu, mahasiswa melihat course tempatnya terdaftar.  
**Parameter query:** `page` (opsional, default `1`).

```bash
curl "$API/courses?page=1" -H "Accept: application/json" \
	-H "Authorization: Bearer $TOKEN_DOSEN"
```

**Sukses `200`:**

```json
{
	"data": [
		{
			"id": 3,
			"code": "SI2514001",
			"name": "Pemrograman Web",
			"description": "Materi dasar pemrograman web.",
			"sks": 3,
			"status": "active",
			"lecturer": {
				"id": 2,
				"name": "Dosen Demo",
				"email": "dosen@kampuslms.test",
				"role": "dosen",
				"nim_nip": "198801012022011001",
				"created_at": "2026-10-02T10:00:00+00:00"
			},
			"counts": { "materials": 3, "assignments": 3 },
			"created_at": "2026-10-02T10:00:00+00:00"
		}
	],
	"meta": { "current_page": 1, "last_page": 1, "total": 1 }
}
```

**Gagal `401`:** `{"message":"Unauthenticated."}`.

### Detail mata kuliah

**Method / URI:** `GET /api/v1/courses/{course}`  
**Peran:** Admin, dosen pengampu, atau mahasiswa terdaftar.  
**Parameter path:** `course` (ID mata kuliah). Tidak ada body/query.

```bash
curl "$API/courses/3" -H "Accept: application/json" \
	-H "Authorization: Bearer $TOKEN_DOSEN"
```

**Sukses `200`:** Resource mata kuliah dalam `data`, dengan field yang sama seperti satu item pada respons daftar.

```json
{
	"data": {
		"id": 3,
		"code": "SI2514001",
		"name": "Pemrograman Web",
		"description": "Materi dasar pemrograman web.",
		"sks": 3,
		"status": "active",
		"lecturer": { "id": 2, "name": "Dosen Demo", "email": "dosen@kampuslms.test", "role": "dosen", "nim_nip": "198801012022011001", "created_at": "2026-10-02T10:00:00+00:00" },
		"counts": { "materials": 3, "assignments": 3 },
		"created_at": "2026-10-02T10:00:00+00:00"
	}
}
```

**Gagal `403` (tidak terdaftar/tidak mengampu):**

```json
{"message":"Anda tidak memiliki akses ke sumber daya ini."}
```

### Materi mata kuliah

**Method / URI:** `GET /api/v1/courses/{course}/materials`  
**Peran:** Admin, dosen pengampu, atau mahasiswa terdaftar.  
**Parameter path:** `course` (ID mata kuliah). **Query:** `page` (opsional).

```bash
curl "$API/courses/3/materials?page=1" -H "Accept: application/json" \
	-H "Authorization: Bearer $TOKEN_DOSEN"
```

**Sukses `200`:**

```json
{
	"data": [
		{
			"id": 5,
			"course_id": 3,
			"uploaded_by": 2,
			"title": "Pengantar HTML",
			"description": "Materi pengantar.",
			"type": "file",
			"original_name": "html-dasar.pdf",
			"file_size": 250000,
			"mime_type": "application/pdf",
			"external_url": null,
			"uploader": {
				"id": 2,
				"name": "Dosen Demo",
				"email": "dosen@kampuslms.test",
				"role": "dosen",
				"nim_nip": "198801012022011001",
				"created_at": "2026-10-02T10:00:00+00:00"
			},
			"created_at": "2026-10-02T10:00:00+00:00"
		}
	],
	"meta": { "current_page": 1, "last_page": 1, "total": 1 }
}
```

`file_path` tidak dikirim API. Untuk materi bertipe `link`, `external_url` berisi URL dan field file dapat bernilai `null`.

**Gagal `403`:** `{"message":"Anda tidak memiliki akses ke sumber daya ini."}`.

### Tugas mata kuliah

**Method / URI:** `GET /api/v1/courses/{course}/assignments`  
**Peran:** Admin, dosen pengampu, atau mahasiswa terdaftar. Mahasiswa hanya menerima tugas berstatus `published`.  
**Parameter path:** `course` (ID mata kuliah). **Query:** `page` (opsional), `status` (`draft` atau `published`, opsional).

```bash
curl "$API/courses/3/assignments?status=published&page=1" \
	-H "Accept: application/json" -H "Authorization: Bearer $TOKEN_DOSEN"
```

**Sukses `200`:**

```json
{
	"data": [
		{
			"id": 8,
			"course_id": 3,
			"created_by": 2,
			"title": "Tugas HTML",
			"instructions": "Buat halaman web sederhana.",
			"due_at": "2026-10-15T23:59:00+00:00",
			"max_score": 100,
			"allow_late": true,
			"status": "published",
			"created_at": "2026-10-02T10:00:00+00:00"
		}
	],
	"meta": { "current_page": 1, "last_page": 1, "total": 1 }
}
```

**Gagal `422` (status selain `draft` atau `published`):**

```json
{
	"message": "Data yang diberikan tidak valid.",
	"errors": { "status": ["The selected status is invalid."] }
}
```

## Tugas dan Pengumpulan

### Membuat tugas

**Method / URI:** `POST /api/v1/assignments`  
**Peran:** Dosen saja, dan hanya pada course yang diampu dosen tersebut.  
**Parameter body:** `course_id` (wajib, ID course), `title` (wajib, maks. 150 karakter), `instructions` (wajib), `due_at` (wajib, tanggal/waktu), `status` (wajib: `draft` atau `published`), `max_score` (opsional, integer 1-100; default 100), `allow_late` (opsional, boolean; default `true`).

```bash
curl -X POST "$API/assignments" \
	-H "Accept: application/json" -H "Content-Type: application/json" \
	-H "Authorization: Bearer $TOKEN_DOSEN" \
	-d '{"course_id":3,"title":"Tugas HTML","instructions":"Buat halaman web sederhana.","due_at":"2026-10-15T23:59:00Z","max_score":100,"allow_late":true,"status":"published"}'
```

**Sukses `201`:**

```json
{
	"data": {
		"id": 8,
		"course_id": 3,
		"created_by": 2,
		"title": "Tugas HTML",
		"instructions": "Buat halaman web sederhana.",
		"due_at": "2026-10-15T23:59:00+00:00",
		"max_score": 100,
		"allow_late": true,
		"status": "published",
		"created_at": "2026-10-02T10:00:00+00:00"
	}
}
```

**Gagal `403` (bukan dosen atau bukan pengampu):** `{"message":"Anda tidak memiliki akses ke sumber daya ini."}`.

### Mengubah tugas

**Method / URI:** `PUT /api/v1/assignments/{assignment}` atau `PATCH /api/v1/assignments/{assignment}`  
**Peran:** Dosen pengampu course tugas.  
**Parameter path:** `assignment` (ID tugas). `PUT` mewajibkan `title`, `instructions`, `due_at`, dan `status`; `PATCH` menerima field tersebut secara opsional. Keduanya menerima `max_score` (integer 1-100 atau null) dan `allow_late` (boolean) secara opsional.

```bash
curl -X PATCH "$API/assignments/8" \
	-H "Accept: application/json" -H "Content-Type: application/json" \
	-H "Authorization: Bearer $TOKEN_DOSEN" \
	-d '{"title":"Tugas HTML Revisi","due_at":"2026-10-20T23:59:00Z"}'
```

**Sukses `200`:** Resource tugas yang diperbarui dalam `data`, dengan struktur sama seperti respons membuat tugas.

**Gagal `403` (bukan dosen pengampu):**

```json
{"message":"Anda tidak memiliki akses ke sumber daya ini."}
```

### Menghapus tugas

**Method / URI:** `DELETE /api/v1/assignments/{assignment}`  
**Peran:** Dosen pengampu course tugas.  
**Parameter path:** `assignment` (ID tugas). Tidak ada body.

```bash
curl -X DELETE "$API/assignments/8" -H "Accept: application/json" \
	-H "Authorization: Bearer $TOKEN_DOSEN"
```

**Sukses `204`:** Tidak ada response body.

**Gagal `403`:** `{"message":"Anda tidak memiliki akses ke sumber daya ini."}`.

### Daftar pengumpulan tugas

**Method / URI:** `GET /api/v1/assignments/{assignment}/submissions`  
**Peran:** Dosen pengampu course tugas.  
**Parameter path:** `assignment` (ID tugas). **Query:** `page` (opsional).

```bash
curl "$API/assignments/8/submissions?page=1" \
	-H "Accept: application/json" -H "Authorization: Bearer $TOKEN_DOSEN"
```

**Sukses `200`:**

```json
{
	"data": [
		{
			"id": 12,
			"assignment_id": 8,
			"user_id": 3,
			"original_name": "jawaban.pdf",
			"file_size": 24576,
			"note": "Jawaban tugas.",
			"submitted_at": "2026-10-10T10:30:00+00:00",
			"is_late": false,
			"student": { "id": 3, "name": "Mahasiswa Demo", "role": "mahasiswa" },
			"grade": null
		}
	],
	"meta": { "current_page": 1, "last_page": 1, "total": 1 }
}
```

**Gagal `403`:** `{"message":"Anda tidak memiliki akses ke sumber daya ini."}`.

### Mengumpulkan atau mengganti jawaban

**Method / URI:** `POST /api/v1/assignments/{assignment}/submissions`  
**Peran:** Mahasiswa yang terdaftar pada course dan tugas berstatus `published`.  
**Parameter path:** `assignment` (ID tugas). **Multipart form:** `file` (wajib, maksimum 10 MB), `note` (opsional, string). Jika sudah ada submission mahasiswa untuk tugas tersebut, file dan submission akan diganti.

```bash
curl -X POST "$API/assignments/8/submissions" \
	-H "Accept: application/json" -H "Authorization: Bearer $TOKEN_MAHASISWA" \
	-F "file=@jawaban.pdf" -F "note=Jawaban tugas"
```

**Sukses:** `201` untuk submission baru, `200` jika submission sebelumnya diganti.

```json
{
	"data": {
		"id": 12,
		"assignment_id": 8,
		"user_id": 3,
		"original_name": "jawaban.pdf",
		"file_size": 24576,
		"note": "Jawaban tugas",
		"submitted_at": "2026-10-10T10:30:00+00:00",
		"is_late": false,
		"student": { "id": 3, "name": "Mahasiswa Demo", "role": "mahasiswa" },
		"grade": null
	}
}
```

`file_path` tidak dikirim API. Jika `allow_late` bernilai `false` dan deadline terlewati, server mengembalikan `422`.

**Gagal `403` (bukan mahasiswa terdaftar atau tugas tidak dipublikasikan):**

```json
{"message":"Anda tidak memiliki akses ke sumber daya ini."}
```

### Memberi atau mengubah nilai

**Method / URI:** `PUT /api/v1/submissions/{submission}/grade`  
**Peran:** Dosen pengampu course dari submission.  
**Parameter path:** `submission` (ID submission). **Body JSON:** `score` (wajib, angka 0 sampai `max_score` tugas), `feedback` (opsional, string atau null).

```bash
curl -X PUT "$API/submissions/12/grade" \
	-H "Accept: application/json" -H "Content-Type: application/json" \
	-H "Authorization: Bearer $TOKEN_DOSEN" \
	-d '{"score":90,"feedback":"Sangat baik."}'
```

**Sukses:** `201` saat nilai dibuat, `200` saat nilai yang ada diperbarui.

```json
{
	"data": {
		"id": 4,
		"submission_id": 12,
		"graded_by": 2,
		"score": "90.00",
		"feedback": "Sangat baik.",
		"graded_at": "2026-10-11T09:00:00+00:00",
		"grader": {
			"id": 2,
			"name": "Dosen Demo",
			"email": "dosen@kampuslms.test",
			"role": "dosen",
			"nim_nip": "198801012022011001",
			"created_at": "2026-10-02T10:00:00+00:00"
		}
	}
}
```

**Gagal `403` (bukan dosen pengampu):** `{"message":"Anda tidak memiliki akses ke sumber daya ini."}`.

## Notifikasi

### Daftar notifikasi

**Method / URI:** `GET /api/v1/notifications`  
**Peran:** Semua role dengan token valid. Hanya notifikasi milik pengguna yang login.  
**Parameter query:** `page` (opsional).

```bash
curl "$API/notifications?page=1" -H "Accept: application/json" \
	-H "Authorization: Bearer $TOKEN_MAHASISWA"
```

**Sukses `200`:**

```json
{
	"data": [
		{
			"id": "550e8400-e29b-41d4-a716-446655440000",
			"type": "App\\Notifications\\TestNotification",
			"data": { "message": "Tugas baru tersedia." },
			"read_at": null,
			"created_at": "2026-10-02T10:00:00+00:00"
		}
	],
	"meta": { "current_page": 1, "last_page": 1, "total": 1 }
}
```

**Gagal `401`:** `{"message":"Unauthenticated."}`.

### Tandai notifikasi sudah dibaca

**Method / URI:** `POST /api/v1/notifications/{notification}/read`  
**Peran:** Semua role dengan token valid; notifikasi harus milik pengguna yang login.  
**Parameter path:** `notification` (ID UUID notifikasi). Tidak ada body.

```bash
curl -X POST "$API/notifications/550e8400-e29b-41d4-a716-446655440000/read" \
	-H "Accept: application/json" -H "Authorization: Bearer $TOKEN_MAHASISWA"
```

**Sukses `200`:**

```json
{
	"data": {
		"id": "550e8400-e29b-41d4-a716-446655440000",
		"type": "App\\Notifications\\TestNotification",
		"data": { "message": "Tugas baru tersedia." },
		"read_at": "2026-10-02T10:05:00+00:00",
		"created_at": "2026-10-02T10:00:00+00:00"
	}
}
```

**Gagal `401`:** `{"message":"Unauthenticated."}`. Notifikasi yang tidak dimiliki pengguna tidak ditemukan dalam scope pengguna tersebut (`404`).
