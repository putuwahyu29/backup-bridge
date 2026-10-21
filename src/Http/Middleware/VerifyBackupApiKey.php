<?php

namespace Bps\AppBackup\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyBackupApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $provided = (string) ($request->header('X-API-Key') ?: $request->header('x-api-key'));

        // Primary key configuration
        $expected = (string) config('app-backup.api_key');

        // Backward compatibility fallbacks for Kadiri & Panjalu
        if (! $expected) {
            $expected = (string) (
                config('services.panjalu_api.key') ?:
                config('services.kadiri_api.key') ?:
                env('BACKUP_API_KEY')
            );
        }

        if (! $expected || ! hash_equals($expected, $provided)) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized: Invalid or missing X-API-Key header.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}