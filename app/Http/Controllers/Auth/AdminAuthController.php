<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.admin-register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ], [
            'name.required'      => 'Nama harus diisi',
            'email.required'     => 'Email harus diisi',
            'email.unique'       => 'Email sudah terdaftar',
            'password.required'  => 'Password harus diisi',
            'password.min'       => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
        ]);

        User::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'role'        => 'admin',
            'is_verified' => false,
        ]);

        return redirect('/admin/login')
            ->with('status', '✅ Pendaftaran berhasil! Tunggu verifikasi dari Super Admin.');
    }

    public function showLogin()
    {
        return view('auth.admin-login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            if (!in_array($user->role, ['admin', 'super_admin'])) {
                Auth::logout();
                return back()->withErrors(['email' => 'Akun ini bukan akun admin.']);
            }

            if ($user->role === 'admin' && !$user->is_verified) {
                Auth::logout();
                return back()->withErrors(['email' => 'Akun kamu belum diverifikasi Super Admin.']);
            }

            $request->session()->regenerate();

            // Set online saat login
            $user->update(['last_seen_at' => now()]);

            if ($user->role === 'super_admin') {
                return redirect('/superadmin/dashboard');
            }
            return redirect('/admin/dashboard');
        }

        return back()->withErrors(['email' => 'Email atau password salah.']);
    }
}