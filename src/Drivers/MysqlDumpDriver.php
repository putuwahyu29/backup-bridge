<?php

namespace Bps\AppBackup\Drivers;

/**
 * Backward-compatibility wrapper for NativeDatabaseDumpDriver.
 */
class MysqlDumpDriver extends NativeDatabaseDumpDriver
{
    // Inherits all multi-database methods (MySQL, PostgreSQL, SQLite, Attachments)
}