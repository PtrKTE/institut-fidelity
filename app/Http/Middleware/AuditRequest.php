<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\AuditService;

class AuditRequest
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (Auth::check() && $request->isMethod('POST')) {
            app(AuditService::class)->log(
                userId: Auth::id(),
                action: 'REQUEST',
                details: $request->method() . ' ' . $request->path(),
                request: $request
            );
        }

        return $response;
    }
}
