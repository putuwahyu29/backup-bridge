<?php

namespace Bps\AppBackup\Contracts;

interface BackupDriverInterface
{
    /**
     * Trigger a fresh backup execution and return the generated file path.
     *
     * @param string $type e.g. 'database' or 'attachments'
     * @return string Absolute file path of the new backup
     */
    public function run(string $type = 'database'): string;

    /**
     * Locate the latest existing backup file for the specified type.
     *
     * @param string $type e.g. 'database' or 'attachments'
     * @return string|null Absolute file path or null if not found
     */
    public function latest(string $type = 'database'): ?string;

    /**
     * Find a specific backup file by its filename.
     *
     * @param string $filename Base filename
     * @param string $type Backup type
     * @return string|null Absolute file path or null if not found
     */
    public function findByFilename(string $filename, string $type = 'database'): ?string;

    /**
     * Get all available backup files.
     *
     * @param string $type 'all', 'database', or 'attachments'
     * @return array List of absolute file paths
     */
    public function all(string $type = 'all'): array;
}