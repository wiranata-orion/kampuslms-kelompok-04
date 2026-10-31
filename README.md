# 🎓 LMS Team 4 - KampusLMS

Platform Learning Management System (LMS) berbasis **Laravel API** dan **Vue 3 SPA (Single Page Application)**. Memanfaatkan **Vue Router** untuk rute frontend dan **Axios** untuk interaksi REST API.

---

## 👥 Daftar Anggota Kelompok
- [Feniria (10241031)](https://github.com/fenii22)
- [Fitriyansyah Wicaksonoaji (10241033)](https://github.com/10241033-svg)
- [Hamzah Wiranata (10241035)](https://github.com/wiranata-orion)
- [Jeshua Austin Daceka (10241037)](https://github.com/10241037-cloud)

---

## 🔑 Akun Demo (Pengujian Lokal)
Sesuai Bagian 4.4 Spesifikasi Proyek:

| Peran | Email | Kata Sandi |
|---|---|---|
| **Admin** | `admin@kampuslms.test` | `password` |
| **Dosen** | `dosen@kampuslms.test` | `password` |
| **Mahasiswa** | `mahasiswa@kampuslms.test` | `password` |

*(Akun demo terbuat secara otomatis saat menjalankan seeder `php artisan migrate --seed`)*

---

## 💻 Cara Penggunaan Git & Repository

### 1. Initial Clone
Buka terminal/PowerShell di folder directory, lalu jalankan:
```shell
git clone https://github.com/wiranata-orion/kampuslms-kelompok-04
cd kampuslms-kelompok-04
```

### 2. Workflow Sebelum Push Kode Baru
Selalu pastikan kode di lokal sudah sinkron dengan repository utama sebelum mengunggah perubahan:
```shell
# Ambil pembaruan terbaru dari branch aktif
git pull origin main

# Tambahkan perubahan, commit, dan push
git add .
git commit -m "deskripsi perubahan fitur"
git push origin main
```

---

## ⚙️ Cara Instalasi & Menjalankan Proyek

### Prasyarat (Prerequisites)

Untuk mempermudah setup environment, disarankan menggunakan **[Laravel Herd](https://herd.laravel.com/)** (Windows & macOS).

Requirements :
- **PHP 8.4+** & **Composer** (Otomatis disertakan jika menggunakan Laravel Herd)
- **[Node.js](https://nodejs.org/) (LTS)** & **NPM**
- **[Git](https://git-scm.com/)**
- **Database Engine**: **SQLite** (siap pakai) atau **MySQL** (via XAMPP / Laragon)

---

### Langkah-Langkah Instalasi Lokal

#### 1. Masuk ke Folder Proyek
```shell
cd kampuslms-kelompok-04
```

#### 2. Install Dependensi Backend (Composer)
Unduh seluruh package PHP dan framework Laravel:
```shell
composer install
```

Jika sebelumnya gagal
```shell
composer install --ignore-platform-reqs
```

#### 3. Install Dependensi Frontend (NPM)
Unduh Vue 3, Vue Router, Axios, Vite, dan Tailwind CSS:

```shell
npm run dev
```

Jika sebelumnya gagal
```shell
npm install --legacy-peer-deps
```

#### 4. Buat File Environment (`.env`)
Salin file template `.env.example` menjadi `.env`:
- **Windows (PowerShell) / Git Bash / Mac / Linux:**
  ```shell
  cp .env.example .env
  ```
- **Windows (CMD):**
  ```cmd
  copy .env.example .env
  ```

#### 5. Generate Application Key
```shell
php artisan key:generate
```

#### 6. Konfigurasi Database & Migrasi

##### Pilihan A: Menggunakan SQLite (Rekomendasi & Praktis)
1. Buka file `.env` dan pastikan baris berikut terkonfigurasi:
   ```env
   DB_CONNECTION=sqlite
   ```
2. Jalankan migrasi dan seeder awal (Laravel akan otomatis membuat file `database/database.sqlite` jika belum ada):
   ```shell
   php artisan migrate --seed
   ```

##### Pilihan B: Menggunakan MySQL (XAMPP / Laragon)
1. Nyalakan service **MySQL** di XAMPP/Laragon.
2. Buat database baru di `phpMyAdmin` (misalnya bernama `kampuslms`).
3. Sesuaikan file `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=kampuslms
   DB_USERNAME=root
   DB_PASSWORD=
   ```
4. Jalankan migrasi dan seeder awal:
   ```shell
   php artisan migrate --seed
   ```

---

### 🚀 Menjalankan Server Development

#### Opsi 1: Menggunakan 2 Terminal (Standar SPA)
Buka 2 jendela terminal secara bersamaan:

- **Terminal 1 (Backend API Laravel):**
  ```shell
  php artisan serve
  ```
- **Terminal 2 (Frontend Vite Compiler):**
  ```shell
  npm run dev
  ```

Akses aplikasi di browser melalui URL: **`http://127.0.0.1:8000`**

#### Opsi 2: Menggunakan Composer Run Dev (All-in-One CLI)
```shell
composer run dev
```
*(Perintah ini akan mengeksekusi server backend dan Vite compiler sekaligus dalam satu terminal).*

---

## 🛠️ Arsitektur & Fitur Utama

- **Backend**: Laravel REST API Controller, Controller laravel yang mengirimkan respon data dalam bentuk format JSON ke frontend
- **Frontend**: Vue, Vue Router, Axios

---

## ⚙️ Penanganan Queue Worker

Proyek ini menggunakan driver `database` untuk menangani proses *background job* dan antrean tugas (*queue*).

### 1. Konfigurasi Environment (`.env`)
Pastikan variabel `QUEUE_CONNECTION` di dalam file `.env` sudah diatur ke `database`:

```env
QUEUE_CONNECTION=database
```

### 2. Menjalankan Queue Worker
Jalankan perintah berikut pada terminal untuk memproses antrean tugas di latar belakang:

```bash
php artisan queue:work --timeout=60 --tries=3
```

**Keterangan Opsi:**
- `--timeout=60`: Menetapkan batas waktu maksimum pemrosesan suatu *job* selama 60 detik sebelum dianggap terhenti (*timed out*).
- `--tries=3`: Menentukan jumlah maksimal percobaan eksekusi suatu *job* jika mengalami kegagalan.

### 3. Penanganan Job Gagal (*Failed Jobs*)
Jika sebuah tugas antrean gagal diproses setelah mencapai batas maksimal percobaan (`$tries`), tugas tersebut akan dicatat ke dalam tabel `failed_jobs`. Gunakan perintah Artisan berikut untuk mengelola *job* yang gagal:

- **Melihat daftar *job* yang gagal:**
  ```bash
  php artisan queue:failed
  ```

- **Mencoba kembali (*retry*) *job* yang gagal:**
  ```bash
  # Menjalankan ulang satu job gagal tertentu berdasarkan ID atau UUID:
  php artisan queue:retry <id-atau-uuid>

  # Menjalankan ulang seluruh job gagal sekaligus:
  php artisan queue:retry all
  ```

- **Menghapus (*forget*) *job* dari daftar gagal:**
  ```bash
  # Menghapus satu job gagal tertentu:
  php artisan queue:forget <id-atau-uuid>

  # Menghapus seluruh riwayat job gagal sekaligus:
  php artisan queue:flush
  ```


## 📌 Pembagian Peran
- **Feniria**: 
- **Fitriyansyah Wicaksonoaji**: 
- **Hamzah Wiranata**: 
- **Jeshua Austin Daceka**: