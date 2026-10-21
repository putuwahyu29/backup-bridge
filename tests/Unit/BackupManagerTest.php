<?php

namespace Bps\AppBackup\Tests\Unit;

use Bps\AppBackup\Facades\BackupBridge;
use Bps\AppBackup\Services\BackupManager;
use Bps\AppBackup\Tests\TestCase;
use Illuminate\Support\Facades\File;

class BackupManagerTest extends TestCase
{
    public function test_format_bytes(): void
    {
        $manager = new BackupManager();

        $this->assertEquals('0 B', $manager->formatBytes(0));
        $this->assertEquals('1 KB', $manager->formatBytes(1024));
        $this->assertEquals('1 MB', $manager->formatBytes(1024 * 1024));
        $this->assertEquals('1.5 MB', $manager->formatBytes((int) (1.5 * 1024 * 1024)));
    }

    public function test_facade_resolves_instance(): void
    {
        $formatted = BackupBridge::formatBytes(2048);
        $this->assertEquals('2 KB', $formatted);
    }

    public function test_metadata_extraction_from_file(): void
    {
        $destDir = config('app-backup.database.destination_path');
        $dummy = $destDir . '/database-app_2026-10-20.sql';
        File::put($dummy, 'SQL DATA CONTENT');

        $manager = new BackupManager();
        $meta = $manager->metadataFromPath($dummy, 'database');

        $this->assertEquals('database', $meta['type']);
        $this->assertEquals('database-app_2026-10-20.sql', $meta['filename']);
        $this->assertEquals(strlen('SQL DATA CONTENT'), $meta['size_bytes']);
        $this->assertEquals(hash('sha256', 'SQL DATA CONTENT'), $meta['sha256']);
    }
}