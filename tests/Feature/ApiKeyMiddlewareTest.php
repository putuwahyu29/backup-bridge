<?php

namespace Bps\AppBackup\Tests\Feature;

use Bps\AppBackup\Tests\TestCase;

class ApiKeyMiddlewareTest extends TestCase
{
    public function test_unauthorized_when_x_api_key_is_missing(): void
    {
        $response = $this->getJson('/api/backups/latest/metadata');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'message' => 'Unauthorized: Invalid or missing X-API-Key header.',
        ]);
    }

    public function test_unauthorized_when_x_api_key_is_invalid(): void
    {
        $response = $this->withHeaders([
            'X-API-Key' => 'wrong-key',
        ])->getJson('/api/backups/latest/metadata');

        $response->assertStatus(401);
    }

    public function test_authorized_when_x_api_key_matches(): void
    {
        $response = $this->withHeaders([
            'X-API-Key' => 'super-secret-backup-key-12345',
        ])->getJson('/api/backups/latest/metadata');

        // Status can be 404 if no backup exists yet or 200, but NOT 401
        $this->assertNotEquals(401, $response->getStatusCode());
    }

    public function test_case_insensitive_header_supported(): void
    {
        $response = $this->withHeaders([
            'x-api-key' => 'super-secret-backup-key-12345',
        ])->getJson('/api/backups/latest/metadata');

        $this->assertNotEquals(401, $response->getStatusCode());
    }
}