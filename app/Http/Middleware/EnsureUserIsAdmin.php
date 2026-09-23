<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check() || ! auth()->user()->isManagement()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akses khusus Admin/Management.'], 403);
            }

            return redirect('/login')->with('error', 'Akses ditolak. Halaman khusus Admin/Management.');
        }

        return $next($request);
    }
}
