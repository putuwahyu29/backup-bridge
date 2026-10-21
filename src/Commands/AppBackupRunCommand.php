<?php

namespace Bps\AppBackup\Commands;

use Bps\AppBackup\Services\BackupManager;
use Illuminate\Console\Command;
use Throwable;

class AppBackupRunCommand extends Command
{
    protected $signature = 'app-backup:run 
                            {type=database : The backup type to execute (database or attachments)}
                            {--fresh : Force generate a fresh backup}';

    protected $description = 'Trigger a backup generation using the configured app-backup driver';

    public function handle(BackupManager $backupManager): int
    {
        $type = $this->argument('type');
        $fresh = $this->option('fresh');

        if (! in_array($type, ['database', 'attachments'])) {
            $this->error("Invalid backup type: '{$type}'. Allowed types: 'database', 'attachments'.");
            return self::FAILURE;
        }

        $this->info("Executing backup for type: '{$type}' (fresh: " . ($fresh ? 'yes' : 'no') . ")...");

        try {
            $path = $backupManager->resolveBackupPath($type, (bool) $fresh);

            if (! $path) {
                $this->error("Backup generation failed to produce a file.");
                return self::FAILURE;
            }

            $metadata = $backupManager->metadataFromPath($path, $type);

            $this->info("Backup completed successfully!");
            $this->table(
                ['Field', 'Value'],
                [
                    ['Type', $metadata['type']],
                    ['Filename', $metadata['filename']],
                    ['Size (Bytes)', number_format($metadata['size_bytes'])],
                    ['SHA-256', $metadata['sha256']],
                    ['Created At', $metadata['created_at']],
                    ['File Path', $path],
                ]
            );

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error("Backup failed with error: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}