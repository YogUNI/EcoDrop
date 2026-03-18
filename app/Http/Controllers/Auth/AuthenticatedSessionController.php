<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        // Set online saat login
        $request->user()->update(['last_seen_at' => now()]);

        return redirect()->intended(route('dashboard', absolute: false))
            ->with('login_success', 'Selamat Datang Kembali! Login berhasil.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        // Set offline saat logout
        if (Auth::check()) {
            Auth::user()->update(['last_seen_at' => null]);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')
            ->with('status', 'Anda telah berhasil keluar. Sampai jumpa lagi!');
    }
}