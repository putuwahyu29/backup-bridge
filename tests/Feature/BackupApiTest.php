<?php

namespace Bps\AppBackup\Tests\Feature;

use Bps\AppBackup\Tests\TestCase;
use Illuminate\Support\Facades\File;

class BackupApiTest extends TestCase
{
    protected array $headers = [
        'X-API-Key' => 'super-secret-backup-key-12345',
    ];

    public function test_metadata_returns_404_when_no_backup_exists(): void
    {
        $response = $this->withHeaders($this->headers)
            ->getJson('/api/backups/latest/metadata?type=database');

        $response->assertStatus(404);
        $response->assertJson([
            'ok' => false,
        ]);
    }

    public function test_metadata_returns_valid_info_when_file_exists(): void
    {
        $destDir = config('app-backup.database.destination_path');
        $dummyFile = $destDir . '/database-test_2026-10-20_12-00-00.sql';
        File::put($dummyFile, 'CREATE TABLE users (id INT);');

        $response = $this->withHeaders($this->headers)
            ->getJson('/api/backups/latest/metadata?type=database');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'type',
            'filename',
            'size_bytes',
            'size_human',
            'sha256',
            'created_at',
        ]);
        $response->assertJson([
            'filename' => basename($dummyFile),
            'sha256' => hash('sha256', 'CREATE TABLE users (id INT);'),
        ]);
    }

    public function test_download_prevents_path_traversal(): void
    {
        $response = $this->withHeaders($this->headers)
            ->getJson('/api/backups/latest/download?filename=../../../../etc/passwd');

        $response->assertStatus(400);
        $response->assertJson([
            'ok' => false,
            'message' => 'Invalid filename parameter.',
        ]);
    }

    public function test_download_returns_file_stream_when_valid(): void
    {
        $destDir = config('app-backup.database.destination_path');
        $dummyFile = $destDir . '/database-test_2026-10-20_12-00-00.sql';
        File::put($dummyFile, 'DUMMY SQL BACKUP CONTENT');

        $response = $this->withHeaders($this->headers)
            ->get('/api/backups/latest/download?filename=' . basename($dummyFile));

        $response->assertStatus(200);
        $this->assertEquals('DUMMY SQL BACKUP CONTENT', $response->streamedContent());
    }
}