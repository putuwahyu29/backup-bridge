<?php

use Bps\AppBackup\Http\Controllers\BackupController;
use Bps\AppBackup\Http\Controllers\ServiceReportController;
use Bps\AppBackup\Http\Middleware\VerifyBackupApiKey;
use Illuminate\Support\Facades\Route;

if (config('app-backup.enabled', true)) {
    $backupPrefix = config('app-backup.routes.prefix', 'api/backups');
    $reportPrefix = config('app-backup.routes.reports_prefix', 'api/service-reports');
    $middleware = array_merge(
        (array) config('app-backup.routes.middleware', ['api']),
        [VerifyBackupApiKey::class]
    );

    Route::middleware($middleware)->group(function () use ($backupPrefix, $reportPrefix) {
        // Backup Endpoints
        Route::prefix($backupPrefix)->group(function () {
            Route::get('/latest/metadata', [BackupController::class, 'metadata'])->name('app-backup.metadata');
            Route::get('/latest/download', [BackupController::class, 'download'])->name('app-backup.download');
        });

        // Service Reports Endpoints
        Route::prefix($reportPrefix)->group(function () {
            Route::post('/', [ServiceReportController::class, 'store'])->name('app-backup.report.store');
            Route::get('/latest', [ServiceReportController::class, 'latest'])->name('app-backup.report.latest');
        });
    });
}
