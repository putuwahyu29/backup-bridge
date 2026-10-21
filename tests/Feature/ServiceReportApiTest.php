<?php

namespace Bps\AppBackup\Tests\Feature;

use Bps\AppBackup\Tests\TestCase;

class ServiceReportApiTest extends TestCase
{
    protected array $headers = [
        'X-API-Key' => 'super-secret-backup-key-12345',
    ];

    public function test_can_submit_valid_success_service_report(): void
    {
        $payload = [
            'service' => 'backup-worker-qnap',
            'status' => 'success',
            'filename' => 'database-test_2026-10-20.sql',
            'sha256' => hash('sha256', 'dummy'),
            'uploaded_object' => '/home/backup/database-test_2026-10-20.sql.zip',
            'uploaded_size' => 1024,
            'meta' => [
                'storage' => 'qnap',
                'duration_seconds' => 3.5,
            ],
        ];

        $response = $this->withHeaders($this->headers)
            ->postJson('/api/service-reports', $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'ok' => true,
            'service' => 'backup-worker-qnap',
            'status' => 'success',
        ]);
    }

    public function test_rejects_report_with_invalid_service_characters(): void
    {
        $payload = [
            'service' => '../hack/service',
            'status' => 'success',
        ];

        $response = $this->withHeaders($this->headers)
            ->postJson('/api/service-reports', $payload);

        $response->assertStatus(422);
    }

    public function test_can_retrieve_latest_report(): void
    {
        $payload = [
            'service' => 'panjalu-worker',
            'status' => 'success',
            'filename' => 'database-panjalu.sql',
            'sha256' => hash('sha256', 'content'),
        ];

        $this->withHeaders($this->headers)
            ->postJson('/api/service-reports', $payload);

        $response = $this->withHeaders($this->headers)
            ->getJson('/api/service-reports/latest?service=panjalu-worker');

        $response->assertStatus(200);
        $response->assertJson([
            'ok' => true,
            'report' => [
                'service' => 'panjalu-worker',
                'status' => 'success',
                'filename' => 'database-panjalu.sql',
            ],
        ]);
    }
}