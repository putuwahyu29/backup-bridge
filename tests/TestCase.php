<?php

namespace Bps\AppBackup\Tests;

use Bps\AppBackup\AppBackupServiceProvider;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected string $testStoragePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testStoragePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'laravel_backup_tests_' . uniqid();
        File::ensureDirectoryExists($this->testStoragePath . '/backup');
        File::ensureDirectoryExists($this->testStoragePath . '/service-reports');

        config([
            'app-backup.enabled' => true,
            'app-backup.api_key' => 'super-secret-backup-key-12345',
            'app-backup.driver' => 'native',
            'app-backup.database.destination_path' => $this->testStoragePath . '/backup',
            'app-backup.report_storage_path' => $this->testStoragePath . '/service-reports',
            'app-backup.reporter_services' => ['*'],
        ]);
    }

    protected function tearDown(): void
    {
        if (File::isDirectory($this->testStoragePath)) {
            File::deleteDirectory($this->testStoragePath);
        }

        parent::tearDown();
    }

    protected function getPackageProviders($app): array
    {
        return [
            AppBackupServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'BackupBridge' => \Bps\AppBackup\Facades\BackupBridge::class,
        ];
    }
}