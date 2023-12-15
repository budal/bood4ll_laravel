<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsSuperAdmin
{
    public function handle(Request $request, \Closure $next): Response
    {
        if ($request->user()->isSuperAdmin()) {
            return $next($request);
        }

        abort(403);
    }
}
