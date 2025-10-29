<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

public function handle($request, Closure $next)
{
    if (auth()->check() && auth()->user()->rol_id == 1) {
        return $next($request);
    }
    return redirect('/unauthorized');
}
