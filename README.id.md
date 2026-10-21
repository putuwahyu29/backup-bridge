<p align="center">
  <h1 align="center">Backup Bridge</h1>
  <p align="center">
    <strong>Jembatan aman, dasbor kontrol visual, dan agen pencadangan multi-database untuk aplikasi Laravel.</strong>
  </p>
  <p align="center">
    <a href="https://packagist.org/packages/putuwahyu29/backup-bridge"><img src="https://img.shields.io/packagist/v/putuwahyu29/backup-bridge.svg?style=flat-square" alt="Latest Version on Packagist"></a>
    <a href="https://packagist.org/packages/putuwahyu29/backup-bridge"><img src="https://img.shields.io/packagist/php-v/putuwahyu29/backup-bridge.svg?style=flat-square" alt="PHP Version"></a>
    <a href="https://packagist.org/packages/putuwahyu29/backup-bridge"><img src="https://img.shields.io/badge/laravel-%5E10.0%20%7C%20%5E11.0%20%7C%20%5E12.0%20%7C%20%5E13.0-red.svg?style=flat-square" alt="Laravel Version"></a>
    <a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square" alt="Software License"></a>
  </p>
</p>

---

<p align="center">
  <a href="README.md">English</a> | <strong>Bahasa Indonesia</strong>
</p>

---

## 📌 Mengapa Backup Bridge?

Paket backup konvensional (seperti `spatie/laravel-backup`) sangat baik untuk menghasilkan file backup di server lokal. Namun, ketika organisasi Anda memiliki **puluhan aplikasi terdistribusi** dan memerlukan sistem terpusat untuk:
1. Menarik backup dari luar server secara aman tanpa membuka akses SSH atau port database publik.
2. Memverifikasi integritas berkas via streaming checksum SHA-256.
3. Mengirimkan file ke **NAS terpusat (QNAP / Synology / S3)** atau **WebDAV (Nextcloud / ownCloud)** melalui worker perantara.
4. Menerima laporan audit dari worker eksternal apakah backup berhasil disimpan di penyimpanan akhir.
5. Memiliki **Pusat Kontrol Visual (Web Dashboard)** langsung di browser tanpa instalasi package admin yang rumit.

**Backup Bridge** hadir sebagai solusi terstandarisasi yang menghubungkan (*bridge*) aplikasi Laravel Anda dengan infrastruktur penyimpanan terpusat.

---

## ✨ Fitur Utama

- 🗄️ **Dukungan Multi-Database Otomatis**: Mendukung **MySQL**, **PostgreSQL (`pg_dump`)**, dan **SQLite** bawaan (*zero-dependency*), serta opsi integrasi penuh `spatie/laravel-backup`.
- 📦 **Arsip Berkas Unggahan (Attachments)**: Otomatis mem-backup folder penyimpanan publik (`storage/app/public`) menjadi arsip `.zip`.
- 🖥️ **Pusat Kontrol Terpadu (Web Dashboard)**: Halaman admin visual bawaan (`/admin/backups`) untuk trigger manual backup, unduh berkas, dan memantau status audit worker.
- ⚡ **100% Kompatibel**: Bekerja mulus di **Full-Stack Blade**, **REST API Headless**, maupun **Laravel Inertia.js** (`?json=1`).
- 🔐 **Keamanan Tingkat Tinggi**:
  - Autentikasi `X-API-Key` dengan constant-time comparison (`hash_equals`) anti *timing-attack*.
  - Anti *Path-Traversal* & sanitasi argumen shell CLI.
  - Berkas tersimpan di penyimpanan privat (`storage/app/private/`).
- 📡 **Audit Callback Loop**: Endpoint penerima status laporan dari worker eksternal (`POST /api/service-reports`).
- 💻 **Artisan CLI**: Perintah `php artisan app-backup:run`, `php artisan app-backup:status`, `php artisan app-backup:install`, dan `php artisan app-backup:key-generate`.

---

## 🏗️ Diagram Alur Arsitektur

```
┌─────────────────────────────────┐
│       Aplikasi Laravel          │
│   (Blade / Inertia / REST API)  │
│                                 │
│   ┌─────────────────────────┐   │
│   │      Backup Bridge      │   │
│   │  - /admin/backups (UI)  │   │
│   │  - /api/backups (API)   │   │
│   └─────────────┬───────────┘   │
└─────────────────┼───────────────┘
                  │ HTTPS (X-API-Key)
                  ▼
┌─────────────────────────────────┐
│     Worker Penarik Backup       │
│     (Docker / Node.js Engine)   │
│     - Verifikasi SHA-256        │
│     - Kompresi ZIP              │
│     - State Deduplikasi         │
└─────────────────┬───────────────┘
                  │ Protokol Storage
                  ▼
┌─────────────────────────────────┐
│       Penyimpanan Terpusat      │
│  - QNAP NAS (File Station CGI)  │
│  - WebDAV (Nextcloud/ownCloud)  │
└─────────────────────────────────┘
```

---

## ⚠️ Penting: Cara Kerja & Pemicu Backup

> [!IMPORTANT]
> **Apakah backup langsung berjalan otomatis begitu package di-install?**
>
> **Tidak seketika.** Package ini sangat *Plug and Play* dari sisi kemudahan pemasangan dan konfigurasi otomatis. Namun, package ini bertindak sebagai **Bridge / Agen** (bukan proses instan yang mengeksekusi dump database saat `composer install`). 
>
> File backup baru akan di-generate ketika **dipicu (triggered)** melalui salah satu dari **4 metode eksekusi** di bawah ini.

---

## 🚀 Panduan Langkah demi Langkah

### 1. Pasang via Composer
```bash
composer require putuwahyu29/backup-bridge
```

### 2. Jalankan Perintah Instalasi Otomatis (One-Command Setup)
Perintah ini akan secara otomatis mem-publish file konfigurasi dan **meng-generate API Key kriptografis** langsung ke berkas `.env` Anda:
```bash
php artisan app-backup:install
```

### 3. Amankan Dashboard Web di Lingkungan Production
Secara default, dashboard menggunakan middleware `['web']`. Untuk membatasi akses hanya kepada admin yang telah login, tambahkan middleware auth aplikasi Anda di `config/app-backup.php`:

```php
'dashboard' => [
    'enabled' => env('APP_BACKUP_DASHBOARD_ENABLED', true),
    'prefix' => env('APP_BACKUP_DASHBOARD_PREFIX', 'admin/backups'),
    'middleware' => ['web', 'auth'], // Tambahkan auth middleware aplikasi Anda
],
```

### 4. Tentukan Cara Menjalankan Backup
Pilih metode yang paling sesuai dengan kebutuhan infrastruktur Anda:

---

## 🔄 4 Cara Menjalankan / Memicu Backup

### Metode 1: Otomasi Terpusat via Remote Worker (Direkomendasikan untuk Multi-App / NAS)
Gunakan microservice pendamping [@putuwahyu29/backup-puller-worker](https://github.com/putuwahyu29/backup-puller-worker). Worker ini bertugas menarik backup secara terjadwal tanpa membebani aplikasi utama:

1. Salin API Key yang dihasilkan di `.env` aplikasi Anda (`APP_BACKUP_API_KEY`).
2. Masukkan ke file konfigurasi worker (`apps.json`):
```json
{
  "apps": [
    {
      "name": "si-petasls",
      "baseUrl": "https://aplikasi-anda.domain.com",
      "apiKey": "bk_xxxxxxxxxxxxxxxxxxxxxxxxxxxx",
      "cron": "0 2 * * *",
      "backupFresh": true
    }
  ]
}
```
Worker akan otomatis memicu backup harian via API, memverifikasi integritas checksum SHA-256, mengompresi ke ZIP, dan mengunggahnya langsung ke QNAP NAS atau Nextcloud/WebDAV.

---

### Metode 2: Otomasi Terjadwal Lokal via Laravel Scheduler (CRON)
Jika Anda **tidak menggunakan worker eksternal** dan ingin aplikasi Laravel mem-backup database secara rutin di server lokal:

**Untuk Laravel 11 / 12 / 13 (`routes/console.php`):**
```php
use Illuminate\Support\Facades\Schedule;

// Backup database setiap hari pukul 02:00 dini hari
Schedule::command('app-backup:run database --fresh')->dailyAt('02:00');

// Backup berkas publik (attachments) setiap hari Minggu pukul 03:00 dini hari
Schedule::command('app-backup:run attachments --fresh')->weeklyOn(0, '03:00');
```

**Untuk Laravel 10 (`app/Console/Kernel.php`):**
```php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('app-backup:run database --fresh')->dailyAt('02:00');
    $schedule->command('app-backup:run attachments --fresh')->weekly();
}
```
*(Pastikan Cron Job server Anda sudah menjalankan `* * * * * php /path-to-app/artisan schedule:run >> /dev/null 2>&1`)*.

---

### Metode 3: Manual via Pusat Kontrol Web Dashboard
1. Buka browser dan kunjungi: `http://aplikasi-anda.test/admin/backups`
2. Klik tombol **"Generate Database Backup"** atau **"Generate Attachments Backup"**.
3. File backup langsung dibuat seketika, dan Anda bisa mengunduhnya langsung atau melihat checksum SHA-256 berkas.

---

### Metode 4: Manual via Artisan CLI
Jalankan langsung melalui terminal server Anda:
```bash
# Menghasilkan backup database fresh seketika
php artisan app-backup:run database --fresh

# Menghasilkan arsip zip folder attachments fresh seketika
php artisan app-backup:run attachments --fresh

# Melihat ringkasan status backup lokal & laporan audit worker
php artisan app-backup:status
```

---

## 🗄️ Prasyarat Server & Konfigurasi Multi-Database

Driver bawaan `native` secara otomatis membaca koneksi aktif di `config('database.default')`:

### A. MySQL / MariaDB
- **Prasyarat**: Binary `mysqldump` terpasang di sistem operasi server / container Docker.
- Konfigurasi `.env`:
```env
APP_BACKUP_DRIVER=native
MYSQLDUMP_PATH=mysqldump
```

### B. PostgreSQL
- **Prasyarat**: Binary `pg_dump` terpasang di server / container.
- Konfigurasi `.env`:
```env
APP_BACKUP_DRIVER=native
APP_BACKUP_DB_CONNECTION=pgsql
PG_DUMP_PATH=pg_dump
```

### C. SQLite
- **Prasyarat**: *Zero dependency!* Sistem langsung membuat salinan snapshot berkas `.sqlite`.
- Konfigurasi `.env`:
```env
APP_BACKUP_DRIVER=native
APP_BACKUP_DB_CONNECTION=sqlite
```

### D. Arsip Lampiran (Attachments)
- **Prasyarat**: Ekstensi PHP `zip` (`ext-zip` / `ZipArchive`) aktif di PHP Anda.

---

## 🖥️ Penggunaan di Laravel Inertia.js (Vue/React)

Dashboard controller mendukung response berformat JSON murni jika dipanggil dengan header `Accept: application/json` atau query parameter `?json=1`:

```javascript
// Contoh pemanggilan dari komponen Vue / React Inertia:
const loadBackupData = async () => {
    const res = await axios.get('/admin/backups?json=1');
    console.log(res.data.data); // dbLatest, attLatest, reports, allBackups, stats
};
```

---

## 🔌 API Endpoints untuk Remote Puller Worker

Endpoint berikut dilindungi oleh header `X-API-Key`:

| Method | Endpoint | Deskripsi |
|---|---|---|
| `GET` | `/api/backups/latest/metadata?type={database\|attachments}` | Mengambil metadata (filename, size, sha256 checksum, tanggal). Mendukung parameter `&fresh=1`. |
| `GET` | `/api/backups/latest/download?filename={filename}&type={type}` | Mengunduh binary stream berkas backup secara aman. |
| `POST`| `/api/service-reports` | Menerima laporan status dari worker (sukses/gagal, uploaded size, dll). |
| `GET` | `/api/service-reports/latest?service={service}&status={status}` | Mengambil status laporan audit terakhir. |

---

## 🛡️ Keamanan (Security)

Jika Anda menemukan celah keamanan (*vulnerability*), harap laporkan langsung melalui issue atau kontak pemelihara dan hindari membuka issue publik sebelum patch dirilis.

---

## 📄 Lisensi

Paket ini dirilis di bawah lisensi terbuka [MIT License](LICENSE).  
Dikembangkan oleh **Putu Wahyu & Kontributor**.