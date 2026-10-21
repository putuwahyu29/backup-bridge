<?php

namespace Bps\AppBackup\Services;

use Bps\AppBackup\Contracts\BackupDriverInterface;
use Bps\AppBackup\Drivers\MysqlDumpDriver;
use Bps\AppBackup\Drivers\NativeDatabaseDumpDriver;
use Bps\AppBackup\Drivers\SpatieBackupDriver;
use Bps\AppBackup\Drivers\StorageDiskDriver;
use Exception;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupManager
{
    protected ?BackupDriverInterface $driver = null;

    public function driver(): BackupDriverInterface
    {
        if ($this->driver !== null) {
            return $this->driver;
        }

        $driverName = config('app-backup.driver', 'spatie');

        $this->driver = match (strtolower($driverName)) {
            'native', 'database', 'mysqldump', 'mysql', 'pgsql', 'postgres', 'sqlite' => new NativeDatabaseDumpDriver(),
            'storage' => new StorageDiskDriver(),
            default => new SpatieBackupDriver(),
        };

        return $this->driver;
    }

    public function resolveBackupPath(string $type = 'database', bool $fresh = false): ?string
    {
        $driver = $this->driver();

        if ($fresh) {
            return $driver->run($type);
        }

        return $driver->latest($type);
    }

    public function findBackupPathByFilename(?string $filename, string $type = 'database'): ?string
    {
        if (! $filename) {
            return null;
        }

        return $this->driver()->findByFilename($filename, $type);
    }

    public function metadataFromPath(string $backupPath, string $type = 'database'): array
    {
        if (! File::exists($backupPath)) {
            throw new Exception("Backup file not found at path: {$backupPath}");
        }

        $lastModified = File::lastModified($backupPath);
        $size = File::size($backupPath);

        return [
            'type' => $type,
            'filename' => basename($backupPath),
            'size_bytes' => $size,
            'size_human' => $this->formatBytes($size),
            'sha256' => hash_file('sha256', $backupPath),
            'created_at' => date(DATE_ATOM, $lastModified),
        ];
    }

    public function listAllBackups(): array
    {
        $filePaths = $this->driver()->all('all');
        $results = [];

        foreach ($filePaths as $path) {
            if (File::exists($path) && File::isFile($path)) {
                $base = basename($path);
                $type = str_starts_with($base, 'attachments-') ? 'attachments' : 'database';
                $results[] = $this->metadataFromPath($path, $type);
            }
        }

        return $results;
    }

    public function deleteBackup(string $filename): bool
    {
        $safeFilename = basename($filename);
        $path = $this->findBackupPathByFilename($safeFilename, 'database')
             ?: $this->findBackupPathByFilename($safeFilename, 'attachments');

        if ($path && File::exists($path)) {
            return File::delete($path);
        }

        return false;
    }

    public function downloadResponse(string $backupPath): BinaryFileResponse
    {
        $ext = strtolower(pathinfo($backupPath, PATHINFO_EXTENSION));

        $contentType = match ($ext) {
            'sql' => 'application/sql',
            'gz' => 'application/gzip',
            default => 'application/zip',
        };

        return response()->download(
            $backupPath,
            basename($backupPath),
            ['Content-Type' => $contentType]
        );
    }

    public function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}