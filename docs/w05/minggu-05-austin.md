# Minggu 05 — Authorization & IDOR

**Nama:** Jeshua Austin Daceka
**NIM:** 10241037
**Kelas:** A

---

## READ — Peta Route Sendiri (30 menit)

Saya menjalankan perintah berikut untuk melihat seluruh route yang ada di project:

```bash
php artisan route:list --except-vendor
```

Dari hasil tersebut, saya mencari route yang menggunakan parameter model seperti `{course}`, `{assignment}`, atau parameter lainnya.

Route yang menggunakan parameter model perlu diperhatikan karena parameter tersebut dapat digunakan untuk mengakses data tertentu berdasarkan ID yang diberikan pada URL.

### Daftar Titik Rawan IDOR

| Route | Method | Parameter | Siapa yang seharusnya boleh mengakses | Kondisi saat ini |
|---|---|---|---|---|
| `/courses/{course}` | GET | `course` | User yang memiliki akses ke course | Belum ada pengecekan hak akses |
| `/courses/{course}/edit` | GET | `course` | User yang memiliki hak untuk mengedit course | Belum ada pengecekan hak akses |
| `/courses/{course}` | DELETE | `course` | User yang memiliki hak untuk menghapus course | Belum ada pengecekan hak akses |

### Analisis

Route yang menggunakan parameter `{course}` dapat menerima ID course secara langsung dari URL. Contohnya:

```
/courses/1
/courses/2
/courses/3
```

Laravel akan menggunakan ID tersebut untuk menentukan course yang ingin diakses. Namun, jika tidak ada pengecekan authorization, user dapat mencoba