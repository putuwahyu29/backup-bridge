<?php

namespace Bps\AppBackup\Http\Controllers;

use Bps\AppBackup\Services\BackupManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class BackupController extends Controller
{
    public function __construct(
        protected BackupManager $backupManager
    ) {}

    public function metadata(Request $request): JsonResponse
    {
        $type = (string) $request->query('type', 'database');
        if (! in_array($type, ['database', 'attachments', 'all'], true)) {
            return response()->json([
                'ok' => false,
                'message' => "Invalid backup type '{$type}'. Allowed: database, attachments, all.",
            ], Response::HTTP_BAD_REQUEST);
        }

        $fresh = $request->boolean('fresh', false);

        try {
            $backupPath = $this->backupManager->resolveBackupPath($type, $fresh);

            if (! $backupPath) {
                return response()->json([
                    'ok' => false,
                    'message' => "Backup file for type '{$type}' is not available.",
                ], Response::HTTP_NOT_FOUND);
            }

            $metadata = $this->backupManager->metadataFromPath($backupPath, $type);
            return response()->json($metadata, Response::HTTP_OK);
        } catch (Throwable $e) {
            $errorMessage = config('app.debug') ? $e->getMessage() : 'An error occurred while generating backup metadata.';
            return response()->json([
                'ok' => false,
                'message' => $errorMessage,
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function download(Request $request): BinaryFileResponse|JsonResponse
    {
        $type = (string) $request->query('type', 'database');
        if (! in_array($type, ['database', 'attachments'], true)) {
            $type = 'database';
        }

        $filename = $request->query('filename');
        if ($filename !== null) {
            $filename = (string) $filename;
            if (basename($filename) !== $filename || ! preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Invalid filename parameter.',
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        $fresh = $request->boolean('fresh', false);

        try {
            $backupPath = $this->backupManager->findBackupPathByFilename($filename, $type);

            if (! $backupPath) {
                $backupPath = $this->backupManager->resolveBackupPath($type, $fresh);
            }

            if (! $backupPath) {
                return response()->json([
                    'ok' => false,
                    'message' => "Backup file for type '{$type}' is not available.",
                ], Response::HTTP_NOT_FOUND);
            }

            return $this->backupManager->downloadResponse($backupPath);
        } catch (Throwable $e) {
            $errorMessage = config('app.debug') ? $e->getMessage() : 'An error occurred while preparing backup download.';
            return response()->json([
                'ok' => false,
                'message' => $errorMessage,
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}