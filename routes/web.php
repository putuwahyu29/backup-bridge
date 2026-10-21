<?php

use Bps\AppBackup\Http\Controllers\BackupDashboardController;
use Illuminate\Support\Facades\Route;

if (config('app-backup.dashboard.enabled', true)) {
    $dashboardPrefix = config('app-backup.dashboard.prefix', 'admin/backups');
    $middleware = (array) config('app-backup.dashboard.middleware', ['web']);

    Route::middleware($middleware)->prefix($dashboardPrefix)->group(function () {
        Route::get('/', [BackupDashboardController::class, 'index'])->name('app-backup.dashboard.index');
        Route::post('/run', [BackupDashboardController::class, 'run'])->name('app-backup.dashboard.run');
        Route::get('/download', [BackupDashboardController::class, 'download'])->name('app-backup.dashboard.download');
        Route::post('/delete', [BackupDashboardController::class, 'delete'])->name('app-backup.dashboard.delete');
    });
}
