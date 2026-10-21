# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-10-20

### Added
- Standardized API endpoints for remote backup pullers:
  - `GET /api/backups/latest/metadata`
  - `GET /api/backups/latest/download`
  - `POST /api/service-reports`
  - `GET /api/service-reports/latest`
- Built-in multi-database `NativeDatabaseDumpDriver`:
  - Automatic detection of MySQL, PostgreSQL (`pg_dump`), and SQLite (snapshot copy).
- Attachments ZIP driver for user uploads storage (`storage/app/public`).
- Pluggable driver support (`spatie`, `native`, `storage`).
- Timing-attack safe API Key middleware (`X-API-Key`).
- Dedicated visual Web Dashboard UI (`/admin/backups`) with manual trigger buttons and QNAP audit status.
- JSON response support for Inertia.js and frontend single-page applications (`?json=1`).
- Artisan CLI commands: `php artisan app-backup:run` and `php artisan app-backup:status`.
