<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaffRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $staff = $request->user('web');

        if (! $staff || ! in_array($staff->role, $roles, true)) {
            abort(403, 'Não tem permissão para aceder a esta área.');
        }

        return $next($request);
    }
}
