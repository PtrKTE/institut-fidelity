<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AntiHijack
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $sessionIp = session('auth_ip');
            $sessionUa = session('auth_user_agent');

            if ($sessionIp && $sessionIp !== $request->ip()) {
                Auth::logout();
                $request->session()->invalidate();

                return redirect()->route('login')
                    ->with('error', 'Session invalide (changement d\'adresse IP).');
            }

            if ($sessionUa && $sessionUa !== $request->userAgent()) {
                Auth::logout();
                $request->session()->invalidate();

                return redirect()->route('login')
                    ->with('error', 'Session invalide (changement de navigateur).');
            }
        }

        return $next($request);
    }
}
