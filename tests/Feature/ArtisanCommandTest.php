<?php

namespace Bps\AppBackup\Tests\Feature;

use Bps\AppBackup\Tests\TestCase;

class ArtisanCommandTest extends TestCase
{
    public function test_key_generate_command_outputs_secure_key(): void
    {
        $this->artisan('app-backup:key-generate', ['--show' => true])
            ->expectsOutputToContain('Generated API Key:')
            ->assertSuccessful();
    }

    public function test_status_command_displays_system_health(): void
    {
        $this->artisan('app-backup:status')
            ->expectsOutputToContain('Laravel App Backup Status')
            ->assertSuccessful();
    }
}