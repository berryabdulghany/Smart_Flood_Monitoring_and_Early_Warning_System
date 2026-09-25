<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pelindung area /admin.
 * Sesi diisi AuthController setelah kredensial diverifikasi ke MongoDB via FastAPI.
 */
class PastikanAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $pengguna = $request->session()->get('admin_user');

        if (! $pengguna || ($pengguna['role'] ?? null) !== 'admin') {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
