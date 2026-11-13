## 13.3 Read → Break → Fix → Build
**Target akhir minggu:** Kelompok Anda memakai Git dengan benar, dan Anda telah mereview kode kelompok lain secara individual.

### READ — Bedah riwayat kelompok Anda (30 menit)
Tanpa AI, jawab di docs/minggu-13-<nama>.md:

1. Berapa commit atas nama Anda? Bandingkan dengan anggota lain.
2. Adakah minggu di mana Anda tidak punya commit sama sekali?
3. Berapa persen pesan commit yang mengikuti Conventional Commits?
4. Temukan satu commit Anda sendiri yang pesannya buruk. Tulis ulang seharusnya bagaimana.
5. git log --stat — temukan commit terbesar Anda. Apakah seharusnya dipecah?


148  Winata
*110  Ajiaja_1033* <- *mine*
48  10241037-cloud
46  Hamzah Wiranata
6  wiranata-orion
5  austhints
3  copilot-swe-agent[bot]
1  railway-app[bot]

Commit saya yang pesannya buruk. Karena tidak mengikuti conventional commits, bahkan memiliki format yang berbeda sendiri ketimbang rekan tim.
| * | | | cf1a15d + Skenario lintas-peran/IDOR
| * | | | d5614c6 + scenario test auth
| * | | | 0b6c3ad + route to portalcontroller
| * | | | e5acd43 + All API documentation
| * | | | 89bb1b7  + catatan &  keamanan
| * | | | 2eb866c + tabel published time grade
| * | | | d23f29a + All policies
| * | | | 4fdf62b + published time
| * | | | ef7287b + permission on assignment, course, grade, material, and subsmission
| * | | | ac19cae + request store material & subsmission
| * | | | a0d3a73 + Controller with Policy

Menggunakan simbol **+** sebagai penanda feat/penambahan fitur. Namun tidak ada commit yang besar karena dari awal saya memecahnya untuk memudahkan review.

### BREAK
| # | Yang dicoba | Yang terjadi |
|---|-------------|------------------------|
| 1 | Commit `.env` **palsu** (lihat kotak di atas), lalu `git push` | CI **menolak** |
| 2 | Hapus `.env` dari riwayat dengan `git rm --cached`, commit lagi | Berkas hilang dari HEAD tapi **masih ada di riwayat** |
| 3 | Dua anggota mengubah baris yang sama di `routes/web.php`, merge | Perlu komunikasi soal baris kode yang dipakai. Apakah salah satunya yang digunakan, atau keduanya dipertahankan. |
| 4 | Push langsung ke `main` | tidak memblokir, bisa langsung push ke main |
| 5 | Buka PR yang gagal CI, coba merge | Tombol merge terkunci |
| 6 | Buat commit dengan `git commit --author="Orang Lain <x@x.com>"` | yap it's work |

