<?php

namespace Bps\AppBackup\Commands;

use Illuminate\Console\Command;

class AppBackupInstallCommand extends Command
{
    protected $signature = 'app-backup:install';

    protected $description = 'Install and set up the Backup Bridge package';

    public function handle(): int
    {
        $this->info('🚀 Installing Backup Bridge...');
        $this->newLine();

        // 1. Publish configuration
        $this->info('1. Publishing configuration file...');
        $this->call('vendor:publish', [
            '--tag' => 'app-backup-config',
            '--force' => false,
        ]);

        // 2. Generate API Key if not already present
        $this->newLine();
        $this->info('2. Generating secure API Key for worker authentication...');
        $this->call('app-backup:key-generate', ['--force' => false]);

        $this->newLine();
        $this->info('🎉 Backup Bridge has been installed successfully!');
        $this->line('Web Dashboard is available at: <comment>' . url(config('app-backup.dashboard.prefix', 'admin/backups')) . '</comment>');
        $this->line('You can customize settings anytime in <comment>config/app-backup.php</comment>');

        return self::SUCCESS;
    }
}