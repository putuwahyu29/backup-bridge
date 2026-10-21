<?php

namespace Bps\AppBackup\Commands;

use Bps\AppBackup\Services\BackupManager;
use Bps\AppBackup\Services\ServiceReportManager;
use Illuminate\Console\Command;

class AppBackupStatusCommand extends Command
{
    protected $signature = 'app-backup:status 
                            {service? : Optional specific worker service name to check}';

    protected $description = 'Display latest local backup status and remote worker reports';

    public function handle(BackupManager $backupManager, ServiceReportManager $reportManager): int
    {
        $this->info("=== BPS Laravel App Backup Status ===");
        $this->line("Driver: " . config('app-backup.driver', 'spatie'));
        $this->line("App Identifier: " . config('app-backup.app_name', config('app.name')));
        $this->line("");

        // Check local backups
        $this->comment("Local Backups Available:");
        $dbLatest = $backupManager->driver()->latest('database');
        $attLatest = $backupManager->driver()->latest('attachments');

        $rows = [];
        if ($dbLatest) {
            $meta = $backupManager->metadataFromPath($dbLatest, 'database');
            $rows[] = ['database', $meta['filename'], number_format($meta['size_bytes']) . ' bytes', $meta['created_at']];
        } else {
            $rows[] = ['database', 'NONE', '-', '-'];
        }

        if ($attLatest) {
            $meta = $backupManager->metadataFromPath($attLatest, 'attachments');
            $rows[] = ['attachments', $meta['filename'], number_format($meta['size_bytes']) . ' bytes', $meta['created_at']];
        } else {
            $rows[] = ['attachments', 'NONE', '-', '-'];
        }

        $this->table(['Type', 'Filename', 'Size', 'Created At'], $rows);

        // Check worker reports
        $service = $this->argument('service') ?: 'backup-puller';
        $report = $reportManager->getLatestReport($service, 'latest');

        $this->line("");
        $this->comment("Latest Worker Report for '{$service}':");

        if ($report) {
            $this->table(
                ['Field', 'Value'],
                [
                    ['Service', $report['service']],
                    ['Status', $report['status'] === 'success' ? '<info>SUCCESS</info>' : '<error>FAILED</error>'],
                    ['Backup File', $report['filename'] ?? '-'],
                    ['Uploaded Object', $report['uploaded_object'] ?? '-'],
                    ['Uploaded Size', isset($report['uploaded_size']) ? number_format($report['uploaded_size']) . ' bytes' : '-'],
                    ['Reported At', $report['reported_at'] ?? '-'],
                    ['Error Message', $report['error_message'] ?? 'None'],
                ]
            );
        } else {
            $this->warn("No report found yet for service '{$service}'.");
        }

        return self::SUCCESS;
    }
}