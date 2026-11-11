<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaffIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $staff = $request->user('web');

        if (! $staff || ! $staff->active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')
                ->withErrors(['email' => 'Esta conta interna está inativa. Contacte o administrador.']);
        }

        return $next($request);
    }
}
