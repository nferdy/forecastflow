<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user() || $request->user()->role !== 'admin') {
            abort(403, 'Halaman ini khusus administrator.');
        }

        return $next($request);
    }
}
