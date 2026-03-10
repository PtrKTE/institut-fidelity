<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ClienteAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!session('cliente_id')) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Non authentifie'], 401);
            }
            return redirect()->route('espace-cliente.login');
        }

        return $next($request);
    }
}
