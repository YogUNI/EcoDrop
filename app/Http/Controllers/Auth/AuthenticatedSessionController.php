<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Mail\OtpMail;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    // Tampilkan form login
    public function create(): View
    {
        return view('auth.login');
    }

    // Proses login → validasi kredensial → kirim OTP
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = $request->user();

        // Logout dulu — jangan biarkan user masuk sebelum OTP diverifikasi
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Hapus OTP lama yang belum dipakai
        OtpCode::where('user_id', $user->id)->delete();

        // Generate OTP 6 digit
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Simpan OTP ke database (expired 5 menit)
        OtpCode::create([
            'user_id'    => $user->id,
            'code'       => $otp,
            'expires_at' => now()->addMinutes(5),
            'is_used'    => false,
        ]);

        // Kirim OTP ke email user
        Mail::to($user->email)->send(new OtpMail($otp, $user->name));

        // ✅ PERBAIKAN: Simpan data lengkap ke session untuk halaman OTP
        session([
            'otp_user_id' => $user->id,
            'otp_email'   => $user->email,
            'otp_sent_at' => now()->timestamp, // Dipakai untuk countdown resend di frontend
        ]);

        return redirect()->route('otp.show')
            ->with('info', 'Kode OTP telah dikirim ke email ' . $user->email);
    }

    // Tampilkan form OTP
    public function showOtp(): View
    {
        // Kalau tidak ada session otp_user_id → redirect ke login
        if (!session('otp_user_id')) {
            return redirect()->route('login');
        }

        $userId = session('otp_user_id');
        $user   = User::findOrFail($userId);

        return view('auth.otp', compact('user'));
    }

    // Verifikasi OTP
    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ], [
            'otp.required' => 'Kode OTP harus diisi',
            'otp.size'     => 'Kode OTP harus 6 digit',
        ]);

        $userId = session('otp_user_id');

        if (!$userId) {
            return redirect()->route('login')
                ->withErrors(['otp' => 'Sesi expired, silakan login ulang.']);
        }

        $otpRecord = OtpCode::where('user_id', $userId)
            ->where('code', $request->otp)
            ->where('is_used', false)
            ->first();

        if (!$otpRecord) {
            return back()->withErrors(['otp' => '❌ Kode OTP salah.']);
        }

        if ($otpRecord->isExpired()) {
            return back()->withErrors(['otp' => '⏱️ Kode OTP sudah expired. Silakan login ulang.']);
        }

        // OTP valid → mark as used
        $otpRecord->update(['is_used' => true]);

        // Login user
        Auth::loginUsingId($userId);
        $request->session()->regenerate();
        
        // ✅ PERBAIKAN: Bersihkan SEMUA session OTP
        session()->forget(['otp_user_id', 'otp_email', 'otp_sent_at']);

        // Set online
        Auth::user()->update(['last_seen_at' => now()]);

        // Redirect sesuai role
        $role = Auth::user()->role;
        if ($role === 'admin') {
            return redirect()->route('admin.dashboard')
                ->with('login_success', 'Selamat Datang Kembali! Login berhasil. ✅');
        } elseif ($role === 'super_admin') {
            return redirect()->route('superadmin.dashboard')
                ->with('login_success', 'Selamat Datang Kembali! Login berhasil. ✅');
        }

        return redirect()->route('user.dashboard')
            ->with('login_success', 'Selamat Datang Kembali! Login berhasil. ✅');
    }

    // Kirim ulang OTP
    public function resendOtp(Request $request): RedirectResponse
    {
        $userId = session('otp_user_id');

        if (!$userId) {
            return redirect()->route('login')
                ->withErrors(['otp' => 'Sesi expired, silakan login ulang.']);
        }

        $user = User::findOrFail($userId);

        // Cek apakah OTP terakhir masih baru (cooldown 60 detik)
        $lastOtp = OtpCode::where('user_id', $userId)
            ->latest()
            ->first();

        if ($lastOtp && $lastOtp->created_at->diffInSeconds(now()) < 60) {
            $sisaDetik = 60 - $lastOtp->created_at->diffInSeconds(now());
            return back()->withErrors(['otp' => "⏳ Tunggu {$sisaDetik} detik sebelum kirim ulang OTP."]);
        }

        // Hapus OTP lama
        OtpCode::where('user_id', $userId)->delete();

        // Generate OTP baru
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        OtpCode::create([
            'user_id'    => $user->id,
            'code'       => $otp,
            'expires_at' => now()->addMinutes(5),
            'is_used'    => false,
        ]);

        Mail::to($user->email)->send(new OtpMail($otp, $user->name));

        // ✅ PERBAIKAN: Update session sent_at agar countdown di frontend reset
        session(['otp_sent_at' => now()->timestamp]);

        return back()->with('info', '✅ Kode OTP baru telah dikirim ke ' . $user->email);
    }

    // Logout
    public function destroy(Request $request): RedirectResponse
    {
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