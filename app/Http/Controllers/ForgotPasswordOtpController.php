<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordOtpController extends Controller
{
    // Step 1: Tampilkan form input email
    public function showEmail()
    {
        return view('auth.forgot-password');
    }

    // Step 2: Proses email → kirim OTP
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'Email harus diisi',
            'email.email'    => 'Format email tidak valid',
            'email.exists'   => 'Email tidak terdaftar di EcoDrop',
        ]);

        $user = User::where('email', $request->email)->first();

        // Cek cooldown 60 detik
        $lastOtp = OtpCode::where('user_id', $user->id)
            ->where('type', 'forgot_password')
            ->latest()
            ->first();

        if ($lastOtp && $lastOtp->created_at->diffInSeconds(now()) < 60) {
            $sisa = 60 - $lastOtp->created_at->diffInSeconds(now());
            return back()->withErrors(['email' => "⏳ Tunggu {$sisa} detik sebelum kirim ulang OTP."]);
        }

        // Hapus OTP lama
        OtpCode::where('user_id', $user->id)
            ->where('type', 'forgot_password')
            ->delete();

        // Generate OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        OtpCode::create([
            'user_id'    => $user->id,
            'code'       => $otp,
            'type'       => 'forgot_password',
            'expires_at' => now()->addMinutes(10),
            'is_used'    => false,
        ]);

        Mail::to($user->email)->send(new OtpMail($otp, $user->name));

        // Simpan ke session
        session([
            'fp_user_id'   => $user->id,
            'fp_email'     => $user->email,
            'fp_sent_at'   => now()->timestamp,
        ]);

        return redirect()->route('password.otp.show')
            ->with('info', 'Kode OTP telah dikirim ke ' . $user->email);
    }

    // Step 3: Tampilkan form input OTP
    public function showOtp()
    {
        if (!session('fp_user_id')) {
            return redirect()->route('password.request');
        }

        $email = session('fp_email');
        return view('auth.forgot-otp', compact('email'));
    }

    // Step 4: Verifikasi OTP
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ], [
            'otp.required' => 'Kode OTP harus diisi',
            'otp.size'     => 'Kode OTP harus 6 digit',
        ]);

        $userId = session('fp_user_id');

        if (!$userId) {
            return redirect()->route('password.request')
                ->withErrors(['otp' => 'Sesi expired, silakan ulangi.']);
        }

        $otpRecord = OtpCode::where('user_id', $userId)
            ->where('code', $request->otp)
            ->where('type', 'forgot_password')
            ->where('is_used', false)
            ->first();

        if (!$otpRecord) {
            return back()->withErrors(['otp' => '❌ Kode OTP salah.']);
        }

        if ($otpRecord->isExpired()) {
            return back()->withErrors(['otp' => '⏱️ Kode OTP sudah expired. Silakan ulangi.']);
        }

        // OTP valid → mark as used
        $otpRecord->update(['is_used' => true]);

        // Simpan token verified ke session
        session(['fp_verified' => true]);

        return redirect()->route('password.otp.reset');
    }

    // Step 5: Tampilkan form reset password
    public function showReset()
    {
        if (!session('fp_user_id') || !session('fp_verified')) {
            return redirect()->route('password.request');
        }

        return view('auth.reset-password-otp');
    }

    // Step 6: Proses reset password
    public function resetPassword(Request $request)
    {
        if (!session('fp_user_id') || !session('fp_verified')) {
            return redirect()->route('password.request');
        }

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.required'  => 'Password baru harus diisi',
            'password.min'       => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
        ]);

        $user = User::findOrFail(session('fp_user_id'));
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Bersihkan session
        session()->forget(['fp_user_id', 'fp_email', 'fp_sent_at', 'fp_verified']);

        // Hapus semua OTP forgot_password user ini
        OtpCode::where('user_id', $user->id)
            ->where('type', 'forgot_password')
            ->delete();

        return redirect()->route('login')
            ->with('status', '✅ Password berhasil direset! Silakan login dengan password baru.');
    }

    // Resend OTP
    public function resendOtp(Request $request)
    {
        $userId = session('fp_user_id');

        if (!$userId) {
            return redirect()->route('password.request');
        }

        $user = User::findOrFail($userId);

        // Cek cooldown
        $lastOtp = OtpCode::where('user_id', $userId)
            ->where('type', 'forgot_password')
            ->latest()
            ->first();

        if ($lastOtp && $lastOtp->created_at->diffInSeconds(now()) < 60) {
            $sisa = 60 - $lastOtp->created_at->diffInSeconds(now());
            return back()->withErrors(['otp' => "⏳ Tunggu {$sisa} detik sebelum kirim ulang."]);
        }

        // Hapus OTP lama → generate baru
        OtpCode::where('user_id', $userId)->where('type', 'forgot_password')->delete();

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        OtpCode::create([
            'user_id'    => $user->id,
            'code'       => $otp,
            'type'       => 'forgot_password',
            'expires_at' => now()->addMinutes(10),
            'is_used'    => false,
        ]);

        Mail::to($user->email)->send(new OtpMail($otp, $user->name));
        session(['fp_sent_at' => now()->timestamp]);

        return back()->with('info', '✅ OTP baru telah dikirim ke ' . $user->email);
    }
}