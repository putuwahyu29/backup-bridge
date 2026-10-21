<?php

namespace Bps\AppBackup\Http\Controllers;

use Bps\AppBackup\Services\ServiceReportManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ServiceReportController extends Controller
{
    public function __construct(
        protected ServiceReportManager $reportManager
    ) {}

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'service' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z0-9_-]+$/'],
            'status' => ['required', 'in:success,failed'],
            'filename' => ['nullable', 'string', 'max:255'],
            'sha256' => ['nullable', 'string', 'size:64', 'regex:/^[a-fA-F0-9]{64}$/'],
            'uploaded_object' => ['nullable', 'string', 'max:255'],
            'uploaded_size' => ['nullable', 'integer', 'min:0'],
            'error_message' => ['nullable', 'string', 'max:5000'],
            'meta' => ['nullable', 'array'],
            'reported_at' => ['nullable', 'date'],
        ]);

        try {
            $report = $this->reportManager->storeReport($data);

            return response()->json([
                'ok' => true,
                'service' => $report['service'],
                'status' => $report['status'],
                'stored_at' => $report['stored_at'],
            ], Response::HTTP_CREATED);
        } catch (Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function latest(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'service' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z0-9_-]+$/'],
            'status' => ['nullable', 'in:success,failed,latest'],
        ]);

        $service = $filters['service'];
        $status = $filters['status'] ?? 'latest';

        $report = $this->reportManager->getLatestReport($service, $status);

        if (! $report) {
            return response()->json([
                'ok' => false,
                'message' => "Service report for '{$service}' with status '{$status}' is not available.",
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'ok' => true,
            'report' => $report,
        ], Response::HTTP_OK);
    }
}