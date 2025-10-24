<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Acepta roles por ID o por nombre (si quisieras ampliar)
        if (! in_array((string)$user->ID_Rol, array_map('strval', $roles), true)) {
            abort(403, 'No autorizado.');
        }

        return $next($request);
    }
}
