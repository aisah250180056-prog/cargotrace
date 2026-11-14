<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Memeriksa apakah role pengguna cocok dengan role yang diizinkan (Slide 05 & 13)
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Pastikan user sudah login
        if (! $request->user()) {
            abort(401, 'Unauthenticated.');
        }

        // 2. Periksa apakah role user ada di daftar role yang diizinkan
        if (! in_array($request->user()->role, $roles)) {
            abort(403, 'Akses ditolak: Anda tidak memiliki wewenang untuk halaman/aksi ini.');
        }

        return $next($request);
    }
}