# Panduan Lengkap Deployment KampusLMS di Railway.app

Panduan ini berisi langkah-langkah praktis untuk mendeploy aplikasi Laravel 12 **KampusLMS** bersama database **MySQL bawaan Railway** secara terintegrasi dalam satu project, lengkap dengan solusi untuk berbagai kendala (troubleshooting) yang sering terjadi di lapangan.

---

## Daftar Isi
1. [Langkah 1: Menyiapkan File `Dockerfile` Siap Produksi](#langkah-1-menyiapkan-file-dockerfile-siap-produksi)
2. [Langkah 2: Membuat Project Baru di Railway.app](#langkah-2-membuat-project-baru-di-railwayapp)
3. [Langkah 3: Menambahkan Database MySQL di Railway (1-Click)](#langkah-3-menambahkan-database-mysql-di-railway-1-click)
4. [Langkah 4: Menghubungkan Repository GitHub KampusLMS](#langkah-4-menghubungkan-repository-github-kampuslms)
5. [Langkah 5: Mengonfigurasi Environment Variables](#langkah-5-mengonfigurasi-environment-variables)
6. [Langkah 6: Menghasilkan Domain Publik (HTTPS)](#langkah-6-menghasilkan-domain-publik-https)
7. [Langkah 7: Migrasi & Seeding Otomatis Database](#langkah-7-migrasi--seeding-otomatis-database)
8. [Langkah 8: Pengujian Aplikasi Live & Akun Demo](#langkah-8-pengujian-aplikasi-live--akun-demo)
9. [Troubleshooting: Kasus-Kasus Error yang Sering Terjadi & Solusinya](#-troubleshooting-kasus-kasus-error-yang-sering-terjadi--solusinya)

---

## Langkah 1: Menyiapkan File `Dockerfile` Siap Produksi

Railway mendukung deployment otomatis berbasis Dockerfile. Karena proyek ini menggunakan **Laravel 12** yang membutuhkan **PHP $\ge$ 8.4**, pastikan file `Dockerfile` di akar (*root*) proyek Anda menggunakan konfigurasi terbaru yang bebas konflik MPM dan port dinamis.

Simpan file **`Dockerfile`** di root folder proyek:


```dockerfile
FROM php:8.4-apache

# 1. Install ekstensi sistem & library PHP yang dibutuhkan Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Konfigurasi Apache: aktifkan mod_rewrite, pastikan hanya mpm_prefork yang aktif (hindari error More than one MPM loaded)
RUN a2enmod rewrite \
    && rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* \
    && a2enmod mpm_prefork \
    && sed -ri -e 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# 3. Atur Document Root ke /var/www/html/public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 4. Install Composer resmi
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Salin seluruh kode proyek ke container
WORKDIR /var/www/html
COPY . .

# 6. Install dependensi composer untuk mode produksi (tambahkan --ignore-platform-reqs agar selalu kompatibel)
RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

# 7. Berikan hak akses ke folder storage dan cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80 8080

# 8. Script startup: pastikan hanya mpm_prefork aktif, atur PORT dinamis Railway, clear cache, jalankan migrasi & seed, lalu jalankan Apache
CMD ["sh", "-c", "rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* && export PORT=\"${PORT:-8080}\" && sed -i \"s/Listen .*/Listen $PORT/\" /etc/apache2/ports.conf && sed -i \"s/<VirtualHost \\*:.*>/<VirtualHost \\*:$PORT>/\" /etc/apache2/sites-available/000-default.conf && php artisan config:clear && php artisan route:clear && php artisan view:clear && (php artisan migrate --force || true) && (php artisan db:seed --force || true) && exec apache2-foreground"]
```

Commit dan push ke GitHub (baik branch `production` maupun `main`):
```bash
git add Dockerfile
git commit -m "feat: production-ready Dockerfile for PHP 8.4 with auto-seed and dynamic port"
git push origin main
```
![Screenshot Struktur File](<Screenshot 2026-11-09 184202.png>)

---

## Langkah 2: Membuat Project Baru di Railway.app

1. Buka situs [https://railway.app](https://railway.app).
2. Login menggunakan akun **GitHub** Anda.
3. Di dashboard utama, klik tombol **"New Project"** (atau tombol **"+ Create a New Project"**) berwarna ungu.

![Screenshot New Project Button](<Screenshot 2026-11-09 175813.png>)

---

## Langkah 3: Menambahkan Database MySQL di Railway (1-Click)

1. Pada menu pilihan layanan, pilih **"Provision MySQL"** (atau klik tombol **`+`** di menu kanan kanvas $\rightarrow$ **Database** $\rightarrow$ **Add MySQL**).
2. Railway otomatis membuat satu service MySQL di dalam kanvas proyek Anda dalam waktu ~5 detik dengan status hijau (**Online**).
3. Anda tidak perlu membuat tabel manual melalui menu database, karena Laravel akan mengisinya secara otomatis.

![Screenshot Pilihan Layanan Database](<Screenshot 2026-11-09 185104.png>)

---

## Langkah 4: Menghubungkan Repository GitHub KampusLMS

1. Di dalam kanvas proyek yang sama, klik tombol **"+ Create"** (atau tombol **`+`** di menu kanan kanvas).
2. Pilih opsi **"GitHub Repo"**.
3. Cari dan pilih repository proyek **`kampuslms`** Anda.
4. Pastikan branch yang terhubung adalah **`main`**.
5. Klik **"Deploy Now"**.
6. Lalu setelah berhasil Klik kotak dengan logo github pastikan repository benar dan branch main. serta jika auto deploys tidak otomatis aktif maka aktifkan dengan mengklik tombol "+ Toggle Auto Deploy"
7. Kalau repositori tidak ada dalam daftar, klik **"Configure GitHub App"** di bawah daftar repository dan dibagian repository access pilih only select repositories dan pilih repository kampuslms yang ada. Setelah terhubung, ulangi langkah 3-5.

![Screenshot pilih github repository](<Screenshot 2026-11-09 185310.png>)
![Screenshot github yang dipakai](<Screenshot 2026-11-09 185353.png>)

---

## Langkah 5: Mengonfigurasi Environment Variables

Agar Laravel terhubung ke database MySQL Railway dan berjalan dalam mode produksi yang aman:

1. Klik service aplikasi web **KampusLMS** di kanvas.
2. Buka tab **"Variables"**.
3. Klik tombol **"Raw Editor"** (atau tambahkan satu per satu):
```env
APP_NAME="LaciKampus"
APP_ENV="production"
APP_KEY="base64:IVtH/qLWopDic53bP8ADrWkkcLJyiPa/Y1vNE5e7olU="
APP_DEBUG="false"
APP_URL="https://${{RAILWAY_PUBLIC_DOMAIN}}"
DB_CONNECTION="mysql"
DB_HOST="${{MySQL.MYSQLHOST}}"
DB_PORT="${{MySQL.MYSQLPORT}}"
DB_DATABASE="${{MySQL.MYSQLDATABASE}}"
DB_USERNAME="${{MySQL.MYSQLUSER}}"
DB_PASSWORD="${{MySQL.MYSQLPASSWORD}}"
SESSION_DRIVER="database"
QUEUE_CONNECTION="database"
FILESYSTEM_DISK="local"
```
> 💡 **Fitur Canggih Railway:** Penulisan `${{MySQL.MYSQLHOST}}` otomatis menarik alamat host internal dari service MySQL yang kita buat di Langkah 3. Koneksi antar-service ini gratis bandwidth, aman dari internet luar, dan sangat cepat!

![Screenshot perubahan variables](<Screenshot 2026-11-09 185746.png>)
> 📸 **Keterangan:**
> jika sudah berhasil tampilannya akan seperti ini.

---

## Langkah 6: Menghasilkan Domain Publik (HTTPS)

Secara default, service di Railway belum memiliki URL publik sampai Anda mengaktifkannya:

1. Masih di service aplikasi **KampusLMS**, buka tab **"Settings"**.
2. Gulir ke bawah hingga bagian **"Networking"** $\rightarrow$ **"Public Networking"**.
3. Klik tombol **"Generate Domain"**.
4. Jika muncul kotak dialog *"Enter the port your app is listening on"*:
   - Masukkan angka: **`8080`** (port standar yang sudah dikonfigurasi di `Dockerfile`).
   - Klik tombol **"Generate Domain"**.
5. Railway akan membuatkan domain gratis berekstensi `.up.railway.app` lengkap dengan sertifikat SSL/HTTPS aktif (contoh: `kampuslms-production.up.railway.app`).

![Screenshot domain yang di generate](<Screenshot 2026-11-09 190359.png>)

---

## Langkah 7: Migrasi & Seeding Otomatis Database

Di file `Dockerfile` terbaru kita, perintah migrasi dan seeding data demo sudah otomatis dieksekusi saat container pertama kali menyala:
```bash
php artisan migrate --force && 
php artisan db:seed --force
```

Jika Anda ingin memeriksa migrate & seed database sudah berjalan atau tidak:
1. Di kanvas sebelah kiri, klik kotak MySQL (service database Anda yang berwarna hijau).
2. Buka tab Database (lalu klik sub-tab Data).
3. Di sisi kiri akan muncul daftar tabel yang sudah dibuat:
4. Klik tabel users → Anda akan melihat akun admin@kampus.id, dosen@kampus.id, dan mahasiswa@kampus.id.
5. Klik tabel lainnya anda akan melihat data lainnya.


Error yang terjadi saat proses deployment:
1. ![alt text](<Screenshot 2026-11-09 175444.png>)
2. ![alt text](<Screenshot 2026-11-09 184857.png>)
3. ![Masalah bootstrap](<Screenshot 2026-11-09 190847.png>)
![Solusi bootstrap](<Screenshot 2026-11-09 191427.png>)
4. ![Masalah server](<Screenshot 2026-11-09 191905.png>)