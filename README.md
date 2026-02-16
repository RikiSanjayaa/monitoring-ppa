# Monitoring Penanganan Laporan Polisi (Ditres PPA PPO)

Aplikasi Laravel + Filament untuk monitoring penanganan perkara lintas satker/satwil, dengan pembagian hak akses berdasarkan peran pengguna.

## Teknologi

- Laravel 12
- Filament 3 (panel admin di root path `/`)
- Database: SQLite atau MySQL (melalui konfigurasi `.env`)
- Import/Export Excel: `maatwebsite/excel`
- Export PDF: `barryvdh/laravel-dompdf`

## Fitur Utama

- Otorisasi berbasis peran:
  - `super_admin`: akses penuh seluruh data dan master
  - `admin`: kelola data hanya untuk satker sendiri
  - `atasan`: lihat semua data (read-only)
- Modul utama:
  - Kasus + timeline RTL
  - Petugas
  - Master data (Satker, Perkara, Penyelesaian)
  - Manajemen User
- Dashboard:
  - Kartu statistik ringkas
  - Tabel ringkasan per satker + total
- Import/Export:
  - Import kasus lengkap (Excel)
  - Export data kasus (Excel)
  - Export laporan ringkasan + detail (PDF landscape)

## Struktur Data Inti

- `satkers`
- `users` (kolom `role` dan `satker_id`)
- `perkaras`
- `penyelesaians`
- `petugas`
- `kasus`
- `kasus_petugas` (pivot many-to-many)
- `rtls`

## Menjalankan Lokal

1. Install dependency:

```bash
composer install
```

2. Siapkan environment:

```bash
cp .env.example .env
php artisan key:generate
```

3. Atur koneksi database di `.env`.

4. Migrasi + seed:

```bash
php artisan migrate:fresh --seed
```

5. Jalankan aplikasi:

```bash
php artisan serve
```

Akses:

- `/` langsung ke panel Filament
- `/login` untuk halaman login Filament (jika belum autentikasi)

## Akun Default Seeder

Password seluruh akun default: `password`

- Super Admin: `super_admin@example.com`
- Atasan: `atasan@example.com`
- Admin satker: `admin.{kode_satker_lowercase}@example.com`

Contoh:

- `admin.subdit-1@example.com`
- `admin.polres-lbar@example.com`

## Data Seeder yang Dibuat

Seeder mencakup seluruh tabel inti aplikasi:

- `SatkerSeeder`
- `PerkaraSeeder`
- `PenyelesaianSeeder`
- `UserSeeder`
- `PetugasSeeder`
- `KasusSeeder` (termasuk relasi pivot `kasus_petugas`)
- `RtlSeeder`

## Pengujian

```bash
php artisan test
```

## Deploy ke VPS (Laravel + Filament)

Panduan ini mengikuti praktik dari dokumentasi Laravel Deployment dan Filament Panel Installation/Production Optimization:

- Laravel deployment: https://laravel.com/docs/12.x/deployment
- Filament panels install/production optimize: https://filamentphp.com/docs/3.x/panels/installation

### 1) Siapkan server

- OS Linux (Ubuntu/Debian umum dipakai)
- PHP 8.2+ + extension Laravel standar (`mbstring`, `xml`, `curl`, `pdo`, `fileinfo`, dll)
- Composer
- Nginx
- MySQL/MariaDB (atau SQLite bila sesuai kebutuhan)

### 2) Clone project dan install dependency produksi

```bash
git clone <repo-url> /var/www/monitoring-ppa
cd /var/www/monitoring-ppa
composer install --no-dev --optimize-autoloader
```

### 3) Konfigurasi environment

```bash
cp .env.example .env
php artisan key:generate
```

Lalu atur minimal ini di `.env`:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://domain-anda`
- kredensial database (`DB_*`)
- mail/queue sesuai kebutuhan

### 4) Migrasi database + link storage

```bash
php artisan migrate --force
php artisan storage:link
```

### 5) Optimasi Laravel + Filament

```bash
php artisan optimize
php artisan filament:optimize
```

Catatan:

- `filament:optimize` akan cache komponen Filament + icon cache untuk performa panel.
- Saat rollback/troubleshooting cache, gunakan:

```bash
php artisan optimize:clear
php artisan filament:optimize-clear
```

### 6) Konfigurasi Nginx (contoh)

Pastikan `root` menunjuk ke folder `public`.

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name domain-anda;
    root /var/www/monitoring-ppa/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Reload service setelah ubah config:

```bash
sudo nginx -t && sudo systemctl reload nginx
sudo systemctl reload php8.2-fpm
```

### 7) Permission folder runtime

Web server user harus bisa menulis ke:

- `storage/`
- `bootstrap/cache/`

Contoh cepat:

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 8) Jalankan scheduler & queue worker (disarankan)

- Cron scheduler:

```bash
* * * * * cd /var/www/monitoring-ppa && php artisan schedule:run >> /dev/null 2>&1
```

- Queue worker via Supervisor/systemd bila `QUEUE_CONNECTION` bukan `sync`.

### 9) Buat akun admin Filament

```bash
php artisan make:filament-user
```

Panel aplikasi ada di path root `/`.

### 10) Checklist deploy update (release berikutnya)

```bash
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize
php artisan filament:optimize
php artisan queue:restart
```

## Catatan

## CI/CD Homeserver (Tailscale + Docker Compose)

Workflow deploy tersedia di `.github/workflows/deploy-homeserver.yml`.

Alur:

1. Push ke branch `main`.
2. GitHub Actions menjalankan `pint --test` dan `php artisan test`.
3. Jika lolos, action terkoneksi ke Tailnet lewat Tailscale.
4. Action SSH ke homeserver dan menjalankan `scripts/deploy-homeserver.sh`.
5. Script deploy akan pull branch terbaru, menjalankan `docker compose up -d --build`, lalu `composer install` dan command artisan penting.

### Secrets GitHub yang wajib

- `TS_AUTHKEY`: auth key Tailscale untuk GitHub Actions.
- `HOMESERVER_HOST`: IP/hostname Tailscale homeserver (contoh `100.x.x.x` atau `server.tailnet.ts.net`).
- `HOMESERVER_USER`: user SSH di homeserver.
- `HOMESERVER_SSH_PRIVATE_KEY`: private key untuk SSH ke homeserver.
- `HOMESERVER_APP_DIR`: path project di homeserver (contoh `/opt/monitoring-ppa`).

### Prasyarat di homeserver

- Repo ini sudah di-clone pada `HOMESERVER_APP_DIR`.
- Docker + Docker Compose plugin tersedia.
- File `.env` aplikasi sudah dikonfigurasi untuk environment server.

- `SatkerScope` membatasi query model `Kasus` dan `Petugas` untuk user `admin`.
- Kebijakan akses (policies) diterapkan pada seluruh resource Filament.
- Nilai `Limpah` dimasukkan sebagai opsi `penyelesaian` agar muncul pada ringkasan dan export.
