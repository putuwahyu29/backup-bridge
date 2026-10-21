<?php

namespace Bps\AppBackup\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Bps\AppBackup\Contracts\BackupDriverInterface driver()
 * @method static string|null resolveBackupPath(string  = 'database', bool  = false)
 * @method static string|null findBackupPathByFilename(?string , string  = 'database')
 * @method static array metadataFromPath(string , string  = 'database')
 * @method static array listAllBackups()
 * @method static bool deleteBackup(string )
 * @method static \Symfony\Component\HttpFoundation\BinaryFileResponse downloadResponse(string )
 * @method static string formatBytes(int , int  = 2)
 *
 * @see \Bps\AppBackup\Services\BackupManager
 */
class BackupBridge extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'app-backup.manager';
    }
}