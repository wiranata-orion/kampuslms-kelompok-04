# Catatan build Minggu 3

### Urutan file migrasi

>2026_01_01_000001_create_users_table.php
2026_01_01_000002_create_courses_table.php
2026_01_01_000003_create_course_user_table.php
2026_01_01_000004_create_materials_table.php
2026_01_01_000005_create_assignments_table.php
2026_01_01_000006_create_submissions_table.php
2026_01_01_000007_create_grades_table.php
2026_01_01_000008_create_notifications_table.php

**1 Januari 2026, pukul 00:00:01**

format nama file migration: **YYYY_MM_DD_HHMMSS**

```
2026_01_01_000001
│    │  │  │
│    │  │  └── Jam, menit, detik
│    │  └───── Hari
│    └──────── Bulan
└───────────── Tahun
```
Namun ada satu hal penting: angka itu tidak harus menunjukkan kapan tabel sebenarnya dibuat. Yang paling penting bagi Laravel adal
ah urutan timestamp tersebut.


