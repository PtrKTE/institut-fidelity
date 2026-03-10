<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuditService
{
    public function log(
        ?int $userId,
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?string $details = null,
        ?Request $request = null,
        bool $success = true,
        mixed $beforeState = null,
        mixed $afterState = null,
    ): void {
        $ip = $request?->ip();
        $ua = $request?->userAgent();
        $route = $request?->path();
        $method = $request?->method();

        // Log en base de donnees
        AuditLog::create([
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'details' => $details,
            'ip_address' => $ip,
            'user_agent' => $ua,
            'route' => $route,
            'http_method' => $method,
            'success' => $success,
            'before_state' => $beforeState ? json_encode($beforeState) : null,
            'after_state' => $afterState ? json_encode($afterState) : null,
        ]);

        // Log dans le fichier
        $logLine = sprintf(
            '[%s] %s | User:%s | %s %s | %s | IP:%s',
            now()->format('Y-m-d H:i:s'),
            $action,
            $userId ?? 'null',
            $method ?? '-',
            $route ?? '-',
            $details ?? '-',
            $ip ?? '-'
        );

        Log::channel('audit')->info($logLine);
    }
}
