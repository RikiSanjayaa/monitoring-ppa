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

## Catatan

- `SatkerScope` membatasi query model `Kasus` dan `Petugas` untuk user `admin`.
- Kebijakan akses (policies) diterapkan pada seluruh resource Filament.
- Nilai `Limpah` dimasukkan sebagai opsi `penyelesaian` agar muncul pada ringkasan dan export.
