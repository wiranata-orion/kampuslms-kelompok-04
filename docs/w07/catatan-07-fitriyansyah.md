## 7.3 Read  → Break → Fix → Build
**Target akhir minggu**: KampusLMS punya login penuh, tiga peran yang benar-benar terpisah, dan Policy yang menutup seluruh titik rawan IDOR dari minggu 5.

### READ
### BREAK
### Build
Part Aji:
3. Policy untuk seluruh model (Course, Material, Assignment, Submission, Grade), dipanggil di controller dan dipakai di Blade.
4. Seluruh index disaring di level query sesuai peran.
5. Tabel Titik Rawan IDOR terisi penuh di docs/keamanan.md: tiap baris menyebut Policy atau query yang menutupnya.


Implementasi policy dan query IDOR:

1. Menambahkan policy auto-discovery Laravel 12 di `app/Policies`: Course, Material, Assignment, Submission, Grade, serta User sesuai jawaban rancangan. Controller memakai `Gate::authorize();` tidak ada registrasi manual di `AuthServiceProvider.`
2. Index course kini dibatasi lewat query sesuai role. `scope=all` tidak membuka course lain; mahasiswa hanya melihat course aktif yang diikuti. Pemeriksaan keanggotaan memakai `exists()`.
3. Menerapkan keputusan rancangan terkait tugas draft, satu kali pengumpulan, deadline `422`, larangan revisi, penghapusan course/tugas yang memiliki data tertentu, dan publikasi nilai. Nilai memakai `published_at` melalui migration dan endpoint publish.
4. Contoh controller, Form Request, Blade `@can`, penggunaan flag izin untuk Vue, penjelasan kenapa Blade bukan pengganti otorisasi server, dan tabel IDOR tersedia di **docs/keamanan.md**. Detail resource API menyediakan flag `permissions` sebagai petunjuk UI—bukan pengganti policy.
5. Skenario lintas-peran/IDOR Tugas 7 ada di AuthorizationPolicyTest.php, dengan runner test-authz.sh.