<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\AuditService;

class CheckSessionTimeout
{
    const TIMEOUT = 1800; // 30 minutes

    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $lastActivity = session('last_activity');

            if ($lastActivity && (time() - $lastActivity) > self::TIMEOUT) {
                $userId = Auth::id();
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                app(AuditService::class)->log(
                    userId: $userId,
                    action: 'LOGOUT_TIMEOUT',
                    details: 'Déconnexion pour inactivité',
                    request: $request
                );

                return redirect()->route('login')
                    ->with('error', 'Session expirée pour inactivité.');
            }

            session(['last_activity' => time()]);
        }

        return $next($request);
    }
}
