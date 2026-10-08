## Rancangan Aturan Validasi — Mata Kuliah

### STORE (Tambah Mata Kuliah)

| Field | Aturan | Alasan |
|---|---|---|
| `code` | required, string, max:20, unique:courses,code | Kode wajib diisi, harus unik karena dipakai identifikasi mata kuliah |
| `name` | required, string, max:150 | Nama wajib diisi, dibatasi panjang biar gak kepanjangan di tampilan |
| `description` | nullable, string | Sesuai skema, kolom ini boleh kosong |
| `sks` | required, integer, between:1,6 | Wajib angka, dibatasi 1-6 sesuai kewajaran SKS mata kuliah |
| `lecturer_id` | required, exists:users,id | Wajib diisi, dan harus benar-benar ada user dengan id itu di database, biar gak ada data "yatim" |
| `status` | required, in:draft,active,archived | Wajib diisi, cuma boleh 3 nilai itu |

### UPDATE (Edit Mata Kuliah)

Sama persis seperti STORE, kecuali `code`:

| Field | Aturan | Alasan beda dari STORE |
|---|---|---|
| `code` | required, string, max:20, unique:courses,code,{id_course_yang_diedit} | Perlu mengecualikan course yang lagi diedit dari pengecekan unique |

### Kenapa `unique` di UPDATE butuh perlakuan beda

Kalau aturan `unique:courses,code` dipakai polos sama di UPDATE (tanpa pengecualian), maka waktu mengedit mata kuliah tanpa mengubah kodenya, validasi akan gagal karena kodenya "sudah dipakai" — padahal yang memakai kode itu adalah course yang sama yang sedang diedit. Solusinya, tambahkan pengecualian id course yang sedang diedit ke dalam aturan unique.