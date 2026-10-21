<?php

namespace Bps\AppBackup\Tests\Unit;

use Bps\AppBackup\Services\ServiceReportManager;
use Bps\AppBackup\Tests\TestCase;

class ServiceReportManagerTest extends TestCase
{
    public function test_whitelisting_service(): void
    {
        $manager = new ServiceReportManager();

        config(['app-backup.reporter_services' => ['*']]);
        $this->assertTrue($manager->isServiceAllowed('any-worker'));

        config(['app-backup.reporter_services' => ['panjalu-qnap', 'kadiri-qnap']]);
        $this->assertTrue($manager->isServiceAllowed('panjalu-qnap'));
        $this->assertTrue($manager->isServiceAllowed('PANJALU-QNAP'));
        $this->assertFalse($manager->isServiceAllowed('rogue-worker'));
    }

    public function test_store_and_retrieve_report(): void
    {
        $manager = new ServiceReportManager();

        $data = [
            'service' => 'test-worker',
            'status' => 'success',
            'filename' => 'database-2026.sql',
            'sha256' => hash('sha256', 'dummy'),
            'uploaded_object' => '/nas/backup/database-2026.sql.zip',
            'uploaded_size' => 5000,
        ];

        $report = $manager->storeReport($data);

        $this->assertEquals('test-worker', $report['service']);
        $this->assertEquals('success', $report['status']);

        $retrieved = $manager->getLatestReport('test-worker', 'latest');
        $this->assertNotNull($retrieved);
        $this->assertEquals('test-worker', $retrieved['service']);
        $this->assertEquals('/nas/backup/database-2026.sql.zip', $retrieved['uploaded_object']);
    }
}