<?php

namespace Bps\AppBackup\Drivers;

use Bps\AppBackup\Contracts\BackupDriverInterface;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SpatieBackupDriver implements BackupDriverInterface
{
    public function run(string $type = 'database'): string
    {
        $option = ($type === 'attachments') ? '--only-files' : '--only-db';
        $prefix = ($type === 'attachments') ? 'attachments-' : 'database-';
        $diskName = config('app-backup.storage_disk', 'backup');
        $appName = config('app-backup.app_name', config('app.name', 'LARAVEL'));

        $disk = Storage::disk($diskName);
        $filesBefore = collect($disk->files($appName));

        Log::info("laravel-app-backup [spatie]: Triggering {$type} backup with prefix {$prefix}");

        $exitCode = Artisan::call('backup:run', [
            $option => true,
            '--disable-notifications' => true,
        ]);

        if ($exitCode !== 0) {
            $output = Artisan::output();
            Log::error("laravel-app-backup [spatie]: Backup command failed with exit code {$exitCode}. Output: {$output}");
            throw new Exception("Spatie backup failed: {$output}");
        }

        $filesAfter = collect($disk->files($appName));
        $newFile = $filesAfter->diff($filesBefore)->first();

        if ($newFile) {
            $oldPath = $disk->path($newFile);
            $baseName = basename($newFile);

            // Prefix if not already prefixed
            if (! str_starts_with($baseName, $prefix)) {
                $newFilename = $prefix . $baseName;
                $newPath = dirname($oldPath) . DIRECTORY_SEPARATOR . $newFilename;
                if (@rename($oldPath, $newPath)) {
                    Log::info("laravel-app-backup [spatie]: Renamed backup file to {$newFilename}");
                    return $newPath;
                }
            }

            return $oldPath;
        }

        $latest = $this->latest($type);
        if (! $latest) {
            throw new Exception("Backup command finished but no output file was detected for type: {$type}");
        }

        return $latest;
    }

    public function latest(string $type = 'database'): ?string
    {
        $all = $this->all($type);
        return ! empty($all) ? $all[0] : null;
    }

    public function all(string $type = 'all'): array
    {
        $diskName = config('app-backup.storage_disk', 'backup');
        $appName = config('app-backup.app_name', config('app.name', 'LARAVEL'));

        if (! config("filesystems.disks.{$diskName}")) {
            return [];
        }

        $disk = Storage::disk($diskName);
        $files = $disk->files($appName);

        if (empty($files)) {
            $files = $disk->files();
        }

        if (empty($files)) {
            return [];
        }

        $prefix = match ($type) {
            'attachments' => 'attachments-',
            'database' => 'database-',
            default => null,
        };

        return collect($files)
            ->filter(function ($file) use ($prefix, $type) {
                $base = basename($file);
                if ($prefix) {
                    if ($type === 'database') {
                        // Match 'database-' OR files that do NOT start with 'attachments-'
                        return str_starts_with($base, 'database-') || ! str_starts_with($base, 'attachments-');
                    }
                    return str_starts_with($base, $prefix);
                }
                return true;
            })
            ->sortByDesc(fn ($file) => $disk->lastModified($file))
            ->map(fn ($file) => $disk->path($file))
            ->values()
            ->all();
    }

    public function findByFilename(string $filename, string $type = 'database'): ?string
    {
        $safeFilename = basename($filename);
        if ($safeFilename !== $filename) {
            return null;
        }

        $diskName = config('app-backup.storage_disk', 'backup');
        $appName = config('app-backup.app_name', config('app.name', 'LARAVEL'));

        $disk = Storage::disk($diskName);

        $candidates = [
            "{$appName}/{$safeFilename}",
            $safeFilename,
        ];

        foreach ($candidates as $relPath) {
            if ($disk->exists($relPath)) {
                return $disk->path($relPath);
            }
        }

        // Check fallback direct storage path
        $fallbackPaths = [
            storage_path("app/private/backup/{$appName}/{$safeFilename}"),
            storage_path("app/private/backup/{$safeFilename}"),
            storage_path("app/backup/{$safeFilename}"),
        ];

        foreach ($fallbackPaths as $path) {
            if (File::exists($path) && File::isFile($path)) {
                return $path;
            }
        }

        return null;
    }
}