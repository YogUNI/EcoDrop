<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $user = auth()->user();

        // Cek banned
        if ($user->is_banned) {
            auth()->logout();
            return redirect('/login')
                ->withErrors(['email' => 'Akun kamu telah dinonaktifkan. Hubungi administrator.']);
        }

        // Cek role
        if (!in_array($user->role, $roles)) {
            abort(403, 'Unauthorized');
        }

        // Khusus admin: cek apakah sudah diverifikasi super admin
        if ($user->role === 'admin' && !$user->is_verified) {
            auth()->logout();
            return redirect('/admin/login')
                ->withErrors(['email' => 'Akun admin kamu belum diverifikasi oleh Super Admin.']);
        }

        return $next($request);
    }
}