<?php

namespace Bps\AppBackup\Http\Controllers;

use Bps\AppBackup\Services\BackupManager;
use Bps\AppBackup\Services\ServiceReportManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class BackupDashboardController extends Controller
{
    public function __construct(
        protected BackupManager $backupManager,
        protected ServiceReportManager $reportManager
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $dbPath = $this->backupManager->driver()->latest('database');
        $attPath = $this->backupManager->driver()->latest('attachments');

        $dbLatest = $dbPath ? $this->backupManager->metadataFromPath($dbPath, 'database') : null;
        $attLatest = $attPath ? $this->backupManager->metadataFromPath($attPath, 'attachments') : null;

        $allBackups = $this->backupManager->listAllBackups();
        $reports = $this->reportManager->getAllReports();

        $data = [
            'appName' => config('app-backup.app_name', config('app.name', 'LARAVEL')),
            'appUrl' => config('app.url', url('/')),
            'driver' => config('app-backup.driver', 'native'),
            'storageDisk' => config('app-backup.storage_disk', 'backup'),
            'dbLatest' => $dbLatest,
            'attLatest' => $attLatest,
            'allBackups' => $allBackups,
            'reports' => $reports,
            'stats' => [
                'total_files' => count($allBackups),
                'total_size' => $this->backupManager->formatBytes(array_sum(array_column($allBackups, 'size_bytes'))),
            ],
        ];

        // Inertia or SPA JSON API support
        if ($request->wantsJson() || $request->has('json')) {
            return response()->json([
                'ok' => true,
                'data' => $data,
            ]);
        }

        return view('app-backup::dashboard', $data);
    }

    public function run(Request $request): RedirectResponse|JsonResponse
    {
        $type = $request->input('type', 'database');

        if (! in_array($type, ['database', 'attachments'])) {
            if ($request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => 'Invalid backup type.'], 400);
            }
            return back()->with('error', 'Tipe backup tidak valid.');
        }

        try {
            $path = $this->backupManager->resolveBackupPath($type, fresh: true);
            $filename = basename($path);

            if ($request->wantsJson()) {
                return response()->json([
                    'ok' => true,
                    'message' => "Backup {$type} berhasil dibuat: {$filename}",
                    'filename' => $filename,
                ]);
            }

            return back()->with('success', "Backup {$type} berhasil digenerate: {$filename}");
        } catch (Throwable $e) {
            $err = config('app.debug') ? $e->getMessage() : 'Gagal membuat file backup.';
            if ($request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => $err], 500);
            }

            return back()->with('error', "Gagal membuat backup: {$err}");
        }
    }

    public function download(Request $request): BinaryFileResponse|RedirectResponse
    {
        $request->validate([
            'filename' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_\-\.]+$/'],
            'type' => ['nullable', 'string', 'in:database,attachments'],
        ]);

        $filename = basename((string) $request->query('filename'));
        $type = (string) $request->query('type', 'database');

        $path = $this->backupManager->findBackupPathByFilename($filename, $type);

        if (! $path) {
            return back()->with('error', 'File backup tidak ditemukan.');
        }

        return $this->backupManager->downloadResponse($path);
    }

    public function delete(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'filename' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_\-\.]+$/'],
        ]);

        $filename = basename((string) $request->input('filename'));

        $deleted = $this->backupManager->deleteBackup($filename);

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => $deleted,
                'message' => $deleted ? 'File berhasil dihapus.' : 'File gagal dihapus.',
            ]);
        }

        return $deleted
            ? back()->with('success', "File {$filename} berhasil dihapus.")
            : back()->with('error', "Gagal menghapus file {$filename}.");
    }
}