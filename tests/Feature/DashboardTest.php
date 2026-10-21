<?php

namespace Bps\AppBackup\Tests\Feature;

use Bps\AppBackup\Tests\TestCase;
use Illuminate\Support\Facades\File;

class DashboardTest extends TestCase
{
    public function test_dashboard_renders_view_successfully(): void
    {
        $response = $this->get('/admin/backups');

        $response->assertStatus(200);
        $response->assertSee('Pusat Kontrol Backup');
    }

    public function test_dashboard_returns_json_when_requested(): void
    {
        $response = $this->getJson('/admin/backups');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'ok',
            'data' => [
                'appName',
                'driver',
                'storageDisk',
                'allBackups',
                'reports',
                'stats',
            ],
        ]);
        // Sensitive API key should NOT be present in JSON data
        $this->assertArrayNotHasKey('apiKey', $response->json('data'));
    }

    public function test_dashboard_download_validates_filename(): void
    {
        $response = $this->get('/admin/backups/download?filename=../../secret.txt');

        $response->assertSessionHasErrors(['filename']);
    }

    public function test_dashboard_delete_validates_filename(): void
    {
        $response = $this->post('/admin/backups/delete', [
            'filename' => '../../secret.txt',
        ]);

        $response->assertSessionHasErrors(['filename']);
    }

    public function test_can_delete_existing_backup_file(): void
    {
        $destDir = config('app-backup.database.destination_path');
        $dummyFile = $destDir . '/database-deleteme_2026.sql';
        File::put($dummyFile, 'TO BE DELETED');

        $response = $this->post('/admin/backups/delete', [
            'filename' => basename($dummyFile),
        ]);

        $response->assertRedirect();
        $this->assertFileDoesNotExist($dummyFile);
    }
}