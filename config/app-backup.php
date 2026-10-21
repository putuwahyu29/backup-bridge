<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Enable App Backup Endpoints
    |--------------------------------------------------------------------------
    | Set to false to completely disable all backup and service report API routes.
    */
    'enabled' => env('APP_BACKUP_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | API Security Key
    |--------------------------------------------------------------------------
    | Remote worker requests must supply this key in the 'X-API-Key' HTTP header.
    | Generate a cryptographically secure key with: php artisan app-backup:key-generate
    */
    'api_key' => env('APP_BACKUP_API_KEY', env('BACKUP_API_KEY', '')),

    /*
    |--------------------------------------------------------------------------
    | Route Configurations (API)
    |--------------------------------------------------------------------------
    | Define API route prefixes and middleware groups.
    */
    'routes' => [
        'prefix' => env('APP_BACKUP_ROUTE_PREFIX', 'api/backups'),
        'reports_prefix' => env('APP_BACKUP_REPORT_ROUTE_PREFIX', 'api/service-reports'),
        'middleware' => ['api'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Dedicated Web Dashboard UI Settings
    |--------------------------------------------------------------------------
    | Visual control dashboard accessible via browser at /admin/backups.
    | SECURITY NOTE: In production environments, it is strongly recommended to
    | append your application's authentication middleware (e.g. ['web', 'auth']).
    */
    'dashboard' => [
        'enabled' => env('APP_BACKUP_DASHBOARD_ENABLED', true),
        'prefix' => env('APP_BACKUP_DASHBOARD_PREFIX', 'admin/backups'),
        'middleware' => ['web'], // Example for protected admin: ['web', 'auth']
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Backup Driver
    |--------------------------------------------------------------------------
    | Supported: 'native' (default), 'spatie', 'storage'
    | - 'native': Universal zero-dependency driver (MySQL, PostgreSQL, SQLite).
    | - 'spatie': Leverages spatie/laravel-backup package with auto prefixing.
    | - 'storage': Direct disk inspection of pre-existing backup files.
    */
    'driver' => env('APP_BACKUP_DRIVER', 'native'),

    /*
    |--------------------------------------------------------------------------
    | Process Execution Timeout
    |--------------------------------------------------------------------------
    | Maximum execution time (in seconds) for database dump or zip compression.
    */
    'timeout' => (int) env('APP_BACKUP_TIMEOUT', 3600),

    /*
    |--------------------------------------------------------------------------
    | Storage Disk & App Identifier
    |--------------------------------------------------------------------------
    | The storage disk name defined in config/filesystems.php where backups reside.
    */
    'storage_disk' => env('APP_BACKUP_DISK', 'backup'),

    /*
    | Application name prefix for backup archives (defaults to config('app.name')).
    */
    'app_name' => env('APP_BACKUP_NAME', config('app.name', 'LARAVEL')),

    /*
    |--------------------------------------------------------------------------
    | Native Database Dump Settings
    |--------------------------------------------------------------------------
    | Used when driver is set to 'native' (or 'mysqldump').
    | Automatically detects MySQL, PostgreSQL, or SQLite from connection driver.
    */
    'database' => [
        'connection' => env('APP_BACKUP_DB_CONNECTION', config('database.default', 'mysql')),
        'destination_path' => storage_path('app/private/backup'),
        'mysqldump_binary' => env('MYSQLDUMP_PATH', 'mysqldump'),
        'pg_dump_binary' => env('PG_DUMP_PATH', 'pg_dump'),
    ],

    // Backward-compatibility alias
    'mysqldump' => [
        'binary_path' => env('MYSQLDUMP_PATH', 'mysqldump'),
        'connection' => env('APP_BACKUP_DB_CONNECTION', 'mysql'),
        'destination_path' => storage_path('app/private/backup'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Service Reporting Settings
    |--------------------------------------------------------------------------
    | Whitelist of services allowed to submit roundtrip status reports.
    | Use ['*'] to accept reports from any worker service.
    */
    'reporter_services' => [
        '*',
    ],

    /*
    | Directory where service report status JSON files are stored.
    */
    'report_storage_path' => storage_path('app/private/service-reports'),
];
