<?php

namespace Bps\AppBackup\Commands;

use Illuminate\Console\Command;

class AppBackupKeyGenerateCommand extends Command
{
    protected $signature = 'app-backup:key-generate 
                            {--show : Display the generated key without modifying .env}
                            {--force : Force overwrite key if one already exists}';

    protected $description = 'Generate a cryptographically secure API key for the backup puller worker';

    public function handle(): int
    {
        $key = 'bk_' . bin2hex(random_bytes(24));

        if ($this->option('show')) {
            $this->line('<comment>Generated API Key:</comment> ' . $key);
            return self::SUCCESS;
        }

        $currentKey = config('app-backup.api_key');
        if (! empty($currentKey) && ! $this->option('force')) {
            if (! $this->confirm('An APP_BACKUP_API_KEY already exists in your configuration. Do you wish to overwrite it?')) {
                $this->info('API key generation aborted.');
                return self::SUCCESS;
            }
        }

        $this->writeNewEnvironmentFileWith($key);

        $this->info('API Key for Backup Bridge generated and saved to .env successfully!');
        $this->line("<info>APP_BACKUP_API_KEY</info>=<comment>{$key}</comment>");
        $this->newLine();
        $this->comment('Snippet ready to copy into your worker apps.json:');
        
        $appName = config('app-backup.app_name', config('app.name', 'app'));
        $appUrl = config('app.url', 'http://localhost:8000');

        $this->line(json_encode([
            'name' => strtolower(str_replace(' ', '-', $appName)),
            'serviceName' => 'backup-puller-' . strtolower(str_replace(' ', '-', $appName)),
            'apiBaseUrl' => $appUrl,
            'apiKey' => $key,
            'backupTypes' => ['database', 'attachments'],
            'storage' => 'qnap',
            'qnapDestPath' => '/home/backup/' . strtolower(str_replace(' ', '-', $appName)),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return self::SUCCESS;
    }

    protected function writeNewEnvironmentFileWith(string $key): void
    {
        $envPath = base_path('.env');
        if (! file_exists($envPath)) {
            $this->warn('.env file not found. Please set APP_BACKUP_API_KEY manually in your environment.');
            return;
        }

        $envContent = file_get_contents($envPath);

        if (preg_match('/^APP_BACKUP_API_KEY=.*$/m', $envContent)) {
            $envContent = preg_replace(
                '/^APP_BACKUP_API_KEY=.*$/m',
                'APP_BACKUP_API_KEY=' . $key,
                $envContent
            );
        } else {
            $envContent .= "\n# Backup Bridge API Key\nAPP_BACKUP_API_KEY={$key}\n";
        }

        file_put_contents($envPath, $envContent);
    }
}