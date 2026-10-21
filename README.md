<p align="center">
  <h1 align="center">Backup Bridge</h1>
  <p align="center">
    <strong>A secure bridge, visual control dashboard, and multi-database backup agent for Laravel applications.</strong>
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
  <strong>English</strong> | <a href="README.id.md">Bahasa Indonesia</a>
</p>

---

## 📌 Overview

Conventional backup solutions (such as `spatie/laravel-backup`) excel at generating archive files locally on a single server. However, in enterprise environments managing dozens or hundreds of distributed applications, teams often need a centralized backup workflow that:
1. Pulls backups remotely without exposing raw SSH access or public database ports.
2. Verifies file integrity using streaming SHA-256 checksums.
3. Transfers archives to centralized **NAS storage (QNAP / Synology / S3)** or **WebDAV (Nextcloud / ownCloud)** via autonomous worker services.
4. Receives round-trip audit confirmation callbacks from external workers.
5. Provides a ready-to-use **Visual Web Dashboard** in the browser with zero complex admin panel dependencies.

**Backup Bridge** bridges the gap between individual Laravel apps and centralized backup orchestrators.

---

## ✨ Features

- 🗄️ **Multi-Database Support**: Automatic detection for **MySQL**, **PostgreSQL (`pg_dump`)**, and **SQLite** (zero-dependency file snapshot), with full backward compatibility for `spatie/laravel-backup`.
- 📦 **Public Attachments Backup**: Automatically compresses public storage directories (`storage/app/public`) into a clean `.zip` archive.
- 🖥️ **Built-in Control Dashboard**: Responsive web interface (`/admin/backups`) to trigger manual backups, download dumps, copy SHA-256 hashes, and monitor worker sync status.
- ⚡ **Universal Architecture Support**: Works out of the box with **Full-Stack Blade**, **Headless REST API**, and **Laravel Inertia.js** (`?json=1`).
- 🔐 **Hardened Security**:
  - Constant-time `X-API-Key` verification (`hash_equals`) to prevent timing attacks.
  - Path-traversal protection and shell parameter sanitization.
  - Backups stored in protected private storage (`storage/app/private/`).
- 📡 **Audit Callback Feedback Loop**: Dedicated endpoint (`POST /api/service-reports`) receiving sync status and error logs from puller workers.
- 💻 **Complete Artisan CLI**: Commands including `app-backup:run`, `app-backup:status`, `app-backup:install`, and `app-backup:key-generate`.

---

## 🏗️ Architecture

```
┌─────────────────────────────────┐
│        Laravel Application      │
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
│      Backup Puller Worker       │
│     (Docker / Node.js Engine)   │
│     - SHA-256 Verification      │
│     - Auto ZIP Compression      │
│     - State Deduplication       │
└─────────────────┬───────────────┘
                  │ Storage Protocol
                  ▼
┌─────────────────────────────────┐
│       Centralized Storage       │
│  - QNAP NAS (File Station CGI)  │
│  - WebDAV (Nextcloud/ownCloud)  │
└─────────────────────────────────┘
```

---

## ⚠️ Important: How Backups are Triggered

> [!IMPORTANT]
> **Does installing this package immediately create a backup?**
>
> **Not immediately.** While this package is highly *Plug and Play* in terms of setup and auto-configuration, it operates as a **Bridge / Agent** rather than an instant background cron running upon `composer require`.
>
> Backups are executed only when **triggered** via one of the **4 execution methods** below.

---

## 🚀 Step-by-Step Setup Guide

### 1. Install via Composer
```bash
composer require putuwahyu29/backup-bridge
```

### 2. Run Automatic Installation (One-Command Setup)
This command automatically publishes the configuration and **generates a cryptographically secure API key** directly into your `.env` file:
```bash
php artisan app-backup:install
```

### 3. Secure Web Dashboard in Production
By default, the dashboard uses the `['web']` middleware group. To restrict access to authenticated administrators in production, update `config/app-backup.php`:

```php
'dashboard' => [
    'enabled' => env('APP_BACKUP_DASHBOARD_ENABLED', true),
    'prefix' => env('APP_BACKUP_DASHBOARD_PREFIX', 'admin/backups'),
    'middleware' => ['web', 'auth'], // Add your application auth middleware
],
```

### 4. Choose Your Backup Execution Method
Select the workflow that matches your infrastructure architecture:

---

## 🔄 4 Ways to Trigger / Run Backups

### Method 1: Centralized Automation via Remote Worker (Recommended for Multi-App / NAS)
Use the companion microservice [@putuwahyu29/backup-puller-worker](https://github.com/putuwahyu29/backup-puller-worker). The worker handles scheduled pulls, verification, and uploads without placing ongoing cron load on your web server:

1. Copy the generated `APP_BACKUP_API_KEY` from your application's `.env`.
2. Add your application to the worker's `apps.json`:
```json
{
  "apps": [
    {
      "name": "si-petasls",
      "baseUrl": "https://your-app.domain.com",
      "apiKey": "bk_xxxxxxxxxxxxxxxxxxxxxxxxxxxx",
      "cron": "0 2 * * *",
      "backupFresh": true
    }
  ]
}
```
The worker will automatically trigger fresh backups via API, verify the streaming SHA-256 hash, compress them into ZIP, and upload them to your QNAP NAS or Nextcloud/WebDAV.

---

### Method 2: Local Scheduled Automation via Laravel Scheduler (CRON)
If you **do not use an external worker** and want the Laravel application to regularly back up locally on its own server:

**For Laravel 11 / 12 / 13 (`routes/console.php`):**
```php
use Illuminate\Support\Facades\Schedule;

// Run database backup daily at 02:00 AM
Schedule::command('app-backup:run database --fresh')->dailyAt('02:00');

// Run attachments backup weekly on Sunday at 03:00 AM
Schedule::command('app-backup:run attachments --fresh')->weeklyOn(0, '03:00');
```

**For Laravel 10 (`app/Console/Kernel.php`):**
```php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('app-backup:run database --fresh')->dailyAt('02:00');
    $schedule->command('app-backup:run attachments --fresh')->weekly();
}
```
*(Ensure your server's system cron has `* * * * * php /path-to-app/artisan schedule:run >> /dev/null 2>&1` configured)*.

---

### Method 3: Manual via Web Dashboard
1. Open your browser and navigate to: `http://your-app-domain.test/admin/backups`
2. Click **"Generate Database Backup"** or **"Generate Attachments Backup"**.
3. The backup file will be created on the fly, ready for instant download or SHA-256 inspection.

---

### Method 4: Manual via Artisan CLI
Execute directly from your terminal:
```bash
# Generate fresh database dump immediately
php artisan app-backup:run database --fresh

# Generate fresh attachments zip archive immediately
php artisan app-backup:run attachments --fresh

# Inspect backup status & worker reports
php artisan app-backup:status
```

---

## 🗄️ Server Requirements & Multi-Database Config

The default `native` driver reads your active database connection (`config('database.default')`):

### A. MySQL / MariaDB
- **Requirement**: `mysqldump` binary available on the server / container host PATH.
- Environment (`.env`):
```env
APP_BACKUP_DRIVER=native
MYSQLDUMP_PATH=mysqldump
```

### B. PostgreSQL
- **Requirement**: `pg_dump` binary installed on the server / container.
- Environment (`.env`):
```env
APP_BACKUP_DRIVER=native
APP_BACKUP_DB_CONNECTION=pgsql
PG_DUMP_PATH=pg_dump
```

### C. SQLite
- **Requirement**: *Zero external dependencies!* Directly copies a snapshot of the `.sqlite` file.
- Environment (`.env`):
```env
APP_BACKUP_DRIVER=native
APP_BACKUP_DB_CONNECTION=sqlite
```

### D. Public Attachments Archive
- **Requirement**: PHP `ext-zip` extension (`ZipArchive`) enabled.

---

## 🖥️ Usage in Laravel Inertia.js (Vue/React)

The dashboard controller provides a JSON API response when requested with `Accept: application/json` or query `?json=1`:

```javascript
// Example in a Vue / React Inertia component:
const loadBackupData = async () => {
    const res = await axios.get('/admin/backups?json=1');
    console.log(res.data.data); // dbLatest, attLatest, reports, allBackups, stats
};
```

---

## 🔌 API Endpoints for Remote Puller Worker

Protected by the `X-API-Key` HTTP header:

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/backups/latest/metadata?type={database\|attachments}` | Retrieves metadata (filename, size, sha256 checksum, date). Supports `&fresh=1`. |
| `GET` | `/api/backups/latest/download?filename={filename}&type={type}` | Streams the binary backup archive securely. |
| `POST`| `/api/service-reports` | Receives sync reports from workers (status, uploaded size, errors). |
| `GET` | `/api/service-reports/latest?service={service}&status={status}` | Retrieves the latest audit report. |

---

## 🛡️ Security

If you discover any security vulnerabilities, please contact the maintainers directly and avoid opening public issues before a patch is published.

---

## 📄 License

This package is open-source software licensed under the [MIT License](LICENSE).  
Developed by **Putu Wahyu & Contributors**.