<?php

namespace Bps\AppBackup;

use Bps\AppBackup\Commands\AppBackupInstallCommand;
use Bps\AppBackup\Commands\AppBackupKeyGenerateCommand;
use Bps\AppBackup\Commands\AppBackupRunCommand;
use Bps\AppBackup\Commands\AppBackupStatusCommand;
use Bps\AppBackup\Services\BackupManager;
use Bps\AppBackup\Services\ServiceReportManager;
use Illuminate\Support\ServiceProvider;

class AppBackupServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/app-backup.php',
            'app-backup'
        );

        $this->app->singleton(BackupManager::class, function ($app) {
            return new BackupManager();
        });
        $this->app->alias(BackupManager::class, 'app-backup.manager');

        $this->app->singleton(ServiceReportManager::class, function ($app) {
            return new ServiceReportManager();
        });
        $this->app->alias(ServiceReportManager::class, 'app-backup.reports');
    }

    public function boot(): void
    {
        // Load Blade Views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'app-backup');

        if ($this->app->runningInConsole()) {
            // Publish Configuration
            $this->publishes([
                __DIR__ . '/../config/app-backup.php' => config_path('app-backup.php'),
            ], 'app-backup-config');

            // Publish Views (Optional customization)
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/app-backup'),
            ], 'app-backup-views');

            // Register CLI Commands
            $this->commands([
                AppBackupInstallCommand::class,
                AppBackupKeyGenerateCommand::class,
                AppBackupRunCommand::class,
                AppBackupStatusCommand::class,
            ]);
        }

        // Load Routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
    }
}