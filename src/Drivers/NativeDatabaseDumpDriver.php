<?php

namespace Bps\AppBackup\Drivers;

use Bps\AppBackup\Contracts\BackupDriverInterface;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Process\Process;
use ZipArchive;

class NativeDatabaseDumpDriver implements BackupDriverInterface
{
    public function run(string $type = 'database'): string
    {
        if ($type === 'attachments') {
            return $this->runAttachmentsBackup();
        }

        return $this->runDatabaseBackup();
    }

    protected function runDatabaseBackup(): string
    {
        $connectionName = config('app-backup.database.connection')
            ?: config('app-backup.mysqldump.connection')
            ?: config('database.default', 'mysql');

        $dbConfig = config("database.connections.{$connectionName}", []);
        $driver = strtolower($dbConfig['driver'] ?? 'mysql');

        $destDir = config('app-backup.database.destination_path')
            ?: config('app-backup.mysqldump.destination_path')
            ?: storage_path('app/private/backup');

        File::ensureDirectoryExists($destDir);

        return match ($driver) {
            'pgsql', 'postgres' => $this->dumpPostgres($dbConfig, $destDir),
            'sqlite' => $this->dumpSqlite($dbConfig, $destDir),
            default => $this->dumpMysql($dbConfig, $destDir),
        };
    }

    protected function dumpMysql(array $dbConfig, string $destDir): string
    {
        $databaseName = $dbConfig['database'] ?? env('DB_DATABASE', 'app');
        $username = $dbConfig['username'] ?? env('DB_USERNAME', 'root');
        $password = (string) ($dbConfig['password'] ?? env('DB_PASSWORD', ''));
        $host = $dbConfig['host'] ?? env('DB_HOST', '127.0.0.1');
        $port = (string) ($dbConfig['port'] ?? env('DB_PORT', '3306'));

        $backupFileName = "database-{$databaseName}_" . now()->format('Y-m-d_H-i-s') . '.sql';
        $backupFilePath = rtrim($destDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $backupFileName;

        $binary = config('app-backup.database.mysqldump_binary')
            ?: config('app-backup.mysqldump.binary_path', 'mysqldump');
        $timeout = (int) config('app-backup.timeout', 3600);

        $args = [
            $binary,
            '--host=' . $host,
            '--port=' . $port,
            '--user=' . $username,
            '--single-transaction',
            '--quick',
            '--routines',
            $databaseName,
        ];

        $env = [];
        if ($password !== '') {
            $env['MYSQL_PWD'] = $password;
        }

        Log::info("backup-bridge [mysql]: Running dump for database '{$databaseName}'");

        $fileHandle = fopen($backupFilePath, 'wb');
        if (! $fileHandle) {
            throw new Exception("Unable to open backup file path for writing: {$backupFilePath}");
        }

        $errorBuffer = '';
        try {
            $process = new Process($args, null, $env, null, $timeout);
            $process->run(function ($type, $buffer) use ($fileHandle, &$errorBuffer) {
                if ($type === Process::OUT) {
                    fwrite($fileHandle, $buffer);
                } else {
                    $errorBuffer .= $buffer;
                }
            });

            fclose($fileHandle);
            $fileHandle = null;

            if (! $process->isSuccessful()) {
                if (File::exists($backupFilePath)) {
                    @unlink($backupFilePath);
                }
                $exitCode = $process->getExitCode();
                Log::error("backup-bridge [mysql]: Dump failed. Code {$exitCode}. Stderr: {$errorBuffer}");
                throw new Exception("MySQL dump failed with exit code {$exitCode}: " . trim($errorBuffer));
            }
        } finally {
            if ($fileHandle) {
                fclose($fileHandle);
            }
        }

        return $backupFilePath;
    }

    protected function dumpPostgres(array $dbConfig, string $destDir): string
    {
        $databaseName = $dbConfig['database'] ?? env('DB_DATABASE', 'app');
        $username = $dbConfig['username'] ?? env('DB_USERNAME', 'postgres');
        $password = (string) ($dbConfig['password'] ?? env('DB_PASSWORD', ''));
        $host = $dbConfig['host'] ?? env('DB_HOST', '127.0.0.1');
        $port = (string) ($dbConfig['port'] ?? env('DB_PORT', '5432'));

        $backupFileName = "database-{$databaseName}_" . now()->format('Y-m-d_H-i-s') . '.sql';
        $backupFilePath = rtrim($destDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $backupFileName;

        $binary = config('app-backup.database.pg_dump_binary', 'pg_dump');
        $timeout = (int) config('app-backup.timeout', 3600);

        $args = [
            $binary,
            '--host=' . $host,
            '--port=' . $port,
            '--username=' . $username,
            '--no-password',
            '--format=plain',
            $databaseName,
        ];

        $env = [];
        if ($password !== '') {
            $env['PGPASSWORD'] = $password;
        }

        Log::info("backup-bridge [pgsql]: Running pg_dump for database '{$databaseName}'");

        $fileHandle = fopen($backupFilePath, 'wb');
        if (! $fileHandle) {
            throw new Exception("Unable to open backup file path for writing: {$backupFilePath}");
        }

        $errorBuffer = '';
        try {
            $process = new Process($args, null, $env, null, $timeout);
            $process->run(function ($type, $buffer) use ($fileHandle, &$errorBuffer) {
                if ($type === Process::OUT) {
                    fwrite($fileHandle, $buffer);
                } else {
                    $errorBuffer .= $buffer;
                }
            });

            fclose($fileHandle);
            $fileHandle = null;

            if (! $process->isSuccessful()) {
                if (File::exists($backupFilePath)) {
                    @unlink($backupFilePath);
                }
                $exitCode = $process->getExitCode();
                Log::error("backup-bridge [pgsql]: Dump failed. Code {$exitCode}. Stderr: {$errorBuffer}");
                throw new Exception("PostgreSQL pg_dump failed with exit code {$exitCode}: " . trim($errorBuffer));
            }
        } finally {
            if ($fileHandle) {
                fclose($fileHandle);
            }
        }

        return $backupFilePath;
    }

    protected function dumpSqlite(array $dbConfig, string $destDir): string
    {
        $sourceDb = $dbConfig['database'] ?? database_path('database.sqlite');

        if (! File::exists($sourceDb)) {
            throw new Exception("SQLite database file not found at: {$sourceDb}");
        }

        $baseName = pathinfo($sourceDb, PATHINFO_FILENAME);
        $backupFileName = "database-{$baseName}_" . now()->format('Y-m-d_H-i-s') . '.sqlite';
        $backupFilePath = rtrim($destDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $backupFileName;

        Log::info("backup-bridge [sqlite]: Copying SQLite database to '{$backupFilePath}'");

        if (! File::copy($sourceDb, $backupFilePath)) {
            throw new Exception("Failed to copy SQLite database from {$sourceDb} to {$backupFilePath}");
        }

        return $backupFilePath;
    }

    protected function runAttachmentsBackup(): string
    {
        if (! class_exists(ZipArchive::class)) {
            throw new Exception("PHP extension 'zip' (ZipArchive) is required for attachments backup.");
        }

        $destDir = config('app-backup.database.destination_path')
            ?: config('app-backup.mysqldump.destination_path')
            ?: storage_path('app/private/backup');

        File::ensureDirectoryExists($destDir);

        $appName = config('app-backup.app_name', config('app.name', 'app'));
        $zipFileName = "attachments-{$appName}_" . now()->format('Y-m-d_H-i-s') . '.zip';
        $zipFilePath = rtrim($destDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $zipFileName;

        $sourceDir = storage_path('app/public');
        if (! File::isDirectory($sourceDir)) {
            $sourceDir = storage_path('app');
        }

        Log::info("backup-bridge [attachments]: Creating ZIP of '{$sourceDir}' to '{$zipFilePath}'");

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception("Failed to create ZIP file at {$zipFilePath}");
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (! $file->isDir()) {
                $filePath = $file->getRealPath();
                if ($filePath && str_contains($filePath, 'backup')) {
                    continue;
                }
                if ($filePath) {
                    $relativePath = substr($filePath, strlen($sourceDir) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }
        }

        $zip->close();

        return $zipFilePath;
    }

    public function latest(string $type = 'database'): ?string
    {
        $all = $this->all($type);
        return ! empty($all) ? $all[0] : null;
    }

    public function all(string $type = 'all'): array
    {
        $destDir = config('app-backup.database.destination_path')
            ?: config('app-backup.mysqldump.destination_path')
            ?: storage_path('app/private/backup');

        if (! File::isDirectory($destDir)) {
            return [];
        }

        $files = collect(File::files($destDir));

        if ($type === 'all') {
            return $files
                ->sortByDesc(fn ($file) => $file->getMTime())
                ->map(fn ($file) => $file->getPathname())
                ->values()
                ->all();
        }

        $prefix = ($type === 'attachments') ? 'attachments-' : 'database-';

        return $files
            ->filter(function ($file) use ($prefix, $type) {
                $name = $file->getFilename();
                $ext = strtolower($file->getExtension());

                if ($type === 'attachments') {
                    return str_starts_with($name, $prefix) && $ext === 'zip';
                }

                // database (can be .sql, .sqlite, .gz)
                $isDbExt = in_array($ext, ['sql', 'sqlite', 'gz'], true);
                return (str_starts_with($name, $prefix) || ! str_starts_with($name, 'attachments-')) && $isDbExt;
            })
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->map(fn ($file) => $file->getPathname())
            ->values()
            ->all();
    }

    public function findByFilename(string $filename, string $type = 'database'): ?string
    {
        $safeFilename = basename($filename);
        if ($safeFilename !== $filename || ! preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename)) {
            return null;
        }

        $destDir = config('app-backup.database.destination_path')
            ?: config('app-backup.mysqldump.destination_path')
            ?: storage_path('app/private/backup');

        $targetPath = rtrim($destDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $safeFilename;

        if (File::exists($targetPath) && File::isFile($targetPath)) {
            return $targetPath;
        }

        return null;
    }
}