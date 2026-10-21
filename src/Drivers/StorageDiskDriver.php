<?php

namespace Bps\AppBackup\Drivers;

use Bps\AppBackup\Contracts\BackupDriverInterface;
use Exception;
use Illuminate\Support\Facades\Storage;

class StorageDiskDriver implements BackupDriverInterface
{
    public function run(string $type = 'database'): string
    {
        $latest = $this->latest($type);
        if ($latest) {
            return $latest;
        }

        throw new Exception("StorageDiskDriver does not generate backups automatically. Pre-generate backups or use 'spatie' / 'mysqldump' driver.");
    }

    public function latest(string $type = 'database'): ?string
    {
        $all = $this->all($type);
        return ! empty($all) ? $all[0] : null;
    }

    public function all(string $type = 'all'): array
    {
        $diskName = config('app-backup.storage_disk', 'backup');
        if (! config("filesystems.disks.{$diskName}")) {
            return [];
        }

        $prefix = ($type === 'attachments') ? 'attachments-' : 'database-';
        $disk = Storage::disk($diskName);
        $files = $disk->allFiles();

        return collect($files)
            ->filter(function ($f) use ($prefix, $type) {
                $base = basename($f);
                if ($type === 'attachments') {
                    return str_starts_with($base, $prefix);
                }
                if ($type === 'database') {
                    return str_starts_with($base, $prefix) || ! str_starts_with($base, 'attachments-');
                }
                return true;
            })
            ->sortByDesc(fn ($f) => $disk->lastModified($f))
            ->map(fn ($f) => $disk->path($f))
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
        $disk = Storage::disk($diskName);

        $allFiles = $disk->allFiles();
        foreach ($allFiles as $file) {
            if (basename($file) === $safeFilename) {
                return $disk->path($file);
            }
        }

        return null;
    }
}