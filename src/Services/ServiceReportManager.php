<?php

namespace Bps\AppBackup\Services;

use Exception;
use Illuminate\Support\Facades\File;

class ServiceReportManager
{
    public function isServiceAllowed(string $service): bool
    {
        $allowed = config('app-backup.reporter_services', ['*']);

        if (in_array('*', $allowed, true)) {
            return true;
        }

        return in_array(strtolower($service), array_map('strtolower', $allowed), true);
    }

    public function storeReport(array $data): array
    {
        $service = strtolower((string) $data['service']);
        $service = preg_replace('/[^a-z0-9_-]/', '', $service);

        if (! $service || ! $this->isServiceAllowed($service)) {
            throw new Exception("Service '{$service}' is not permitted to submit reports.");
        }

        $status = in_array($data['status'], ['success', 'failed'], true) ? $data['status'] : 'failed';
        $reportedAt = isset($data['reported_at']) ? now()->parse($data['reported_at']) : now();
        $storedAt = now();

        $report = [
            'service' => $service,
            'status' => $status,
            'filename' => $data['filename'] ?? null,
            'sha256' => $data['sha256'] ?? null,
            'uploaded_object' => $data['uploaded_object'] ?? null,
            'uploaded_size' => $data['uploaded_size'] ?? null,
            'error_message' => $data['error_message'] ?? null,
            'meta' => $data['meta'] ?? null,
            'reported_at' => $reportedAt->toAtomString(),
            'stored_at' => $storedAt->toAtomString(),
        ];

        $baseDir = $this->baseDirectory($service);
        File::ensureDirectoryExists($baseDir);

        File::put(
            $this->statusFilePath($service, 'latest'),
            json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        File::put(
            $this->statusFilePath($service, $status),
            json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        return $report;
    }

    public function getLatestReport(string $service, string $status = 'latest'): ?array
    {
        $service = strtolower((string) $service);
        $service = preg_replace('/[^a-z0-9_-]/', '', $service);

        if (! $service) {
            return null;
        }

        $filePath = $this->statusFilePath($service, $status);

        if (! File::exists($filePath)) {
            return null;
        }

        $decoded = json_decode(File::get($filePath), true);
        return is_array($decoded) ? $decoded : null;
    }

    public function getAllReports(): array
    {
        $customPath = config('app-backup.report_storage_path');
        $base = $customPath ?: storage_path('app/private/service-reports');

        if (! File::isDirectory($base)) {
            return [];
        }

        $directories = File::directories($base);
        $reports = [];

        foreach ($directories as $dir) {
            $service = basename($dir);
            $latest = $this->getLatestReport($service, 'latest');
            if ($latest) {
                $reports[$service] = $latest;
            }
        }

        return $reports;
    }

    protected function baseDirectory(string $service): string
    {
        $safeService = preg_replace('/[^a-z0-9_-]/', '', strtolower($service));
        $customPath = config('app-backup.report_storage_path');
        $base = $customPath ?: storage_path('app/private/service-reports');

        return rtrim($base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $safeService;
    }

    protected function statusFilePath(string $service, string $status): string
    {
        $safeStatus = in_array($status, ['latest', 'success', 'failed'], true) ? $status : 'latest';
        return $this->baseDirectory($service) . DIRECTORY_SEPARATOR . $safeStatus . '.json';
    }
}