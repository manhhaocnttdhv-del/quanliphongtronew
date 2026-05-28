<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectBasedOnRole
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $u = auth()->user();

        if ($u->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($u->hasRole('tenant')) {
            return redirect()->route('tenant.dashboard');
        }

        if ($u->hasRole('customer')) {
            return redirect()->route('booking.index');
        }

        return redirect()->route('home');
    }
}
