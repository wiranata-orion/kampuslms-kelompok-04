### READ --Ukur aplikasi

| Halaman | Jumlah query | Waktu (ms) | Memori |
|---------|-------------:|-----------:|-------:|
| Daftar mata kuliah | 3 | 191,95 | +4 MB |
| Detail mata kuliah | 5 | 15,94 | +0 MB |
| Daftar tugas | 5 | 23,71 | +0 MB |
| Daftar pengumpulan (dosen) | 9 | 56,44 | +2 MB |
| **Rekap nilai** | 7 | 99,98 | +0 MB |
| Dashboard mahasiswa | 2 | 70,09 | +0 MB |
| Dashboard dosen | 2 | 11,86 | +0 MB |

### Kesimpulan

- **Rekap nilai** menggunakan 7 query pada fixture ini; hasil tersebut
  konsisten dengan optimasi eager loading dan pagination yang sudah diterapkan.
- **Daftar pengumpulan** memiliki query terbanyak (9), sehingga menjadi kandidat
  utama untuk pemeriksaan lanjutan jika jumlah submission diperbesar.
- Perbedaan waktu dashboard mahasiswa dan dosen dipengaruhi cache dashboard,
  ukuran data, dan kondisi proses lokal. Pengukuran ulang beberapa kali diperlukan
  sebelum menarik kesimpulan performa production.