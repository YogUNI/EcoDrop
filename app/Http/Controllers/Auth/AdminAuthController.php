<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

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

        // Cek credentials dulu tanpa login
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors(['email' => 'Email atau password salah.']);
        }

        // Cek role
        if (!in_array($user->role, ['admin', 'super_admin'])) {
            return back()->withErrors(['email' => 'Akun ini bukan akun admin.']);
        }

        // Cek verifikasi (khusus role admin biasa)
        if ($user->role === 'admin' && !$user->is_verified) {
            return back()->withErrors(['email' => 'Akun kamu belum diverifikasi Super Admin.']);
        }

        // Simpan remember preference ke session sementara
        $request->session()->put('admin_otp_user_id', $user->id);
        $request->session()->put('admin_otp_remember', $request->boolean('remember'));
        $request->session()->put('admin_otp_email', $user->email);

        // Hapus OTP lama yang belum dipakai
        OtpCode::where('user_id', $user->id)->where('is_used', false)->delete();

        // Generate OTP baru
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        OtpCode::create([
            'user_id'    => $user->id,
            'code'       => $code,
            'expires_at' => now()->addMinutes(10),
            'is_used'    => false,
        ]);

        // Catat timestamp kirim untuk countdown
        $request->session()->put('admin_otp_sent_at', now()->timestamp);

        // Kirim email OTP (via queue)
        Mail::to($user->email)->queue(new OtpMail($code, $user->name));

        return redirect()->route('admin.otp.show');
    }

    // ── OTP ──────────────────────────────────────────────────────────

    public function showAdminOtp()
    {
        // Guard: jika tidak ada session OTP, redirect ke login
        if (!session('admin_otp_user_id')) {
            return redirect()->route('admin.login');
        }

        return view('auth.admin-otp');
    }

    public function verifyAdminOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ], [
            'otp.required' => 'Kode OTP harus diisi.',
            'otp.digits'   => 'Kode OTP harus 6 digit angka.',
        ]);

        $userId = session('admin_otp_user_id');

        if (!$userId) {
            return redirect()->route('admin.login')
                ->with('error', 'Sesi tidak valid. Silakan login kembali.');
        }

        $otpRecord = OtpCode::where('user_id', $userId)
            ->where('is_used', false)
            ->latest()
            ->first();

        // Validasi OTP
        if (!$otpRecord || $otpRecord->isExpired()) {
            return back()->with('error', 'Kode OTP sudah kadaluarsa. Silakan kirim ulang.');
        }

        if ($otpRecord->code !== $request->otp) {
            return back()->with('error', 'Kode OTP salah. Periksa kembali email kamu.');
        }

        // OTP valid → tandai sudah dipakai
        $otpRecord->update(['is_used' => true]);

        // Login user
        $remember = session('admin_otp_remember', false);
        Auth::loginUsingId($userId, $remember);

        $user = Auth::user();

        // Bersihkan semua session OTP
        $request->session()->forget([
            'admin_otp_user_id',
            'admin_otp_remember',
            'admin_otp_email',
            'admin_otp_sent_at',
        ]);

        $request->session()->regenerate();

        // Set online
        $user->update(['last_seen_at' => now()]);

        // Redirect sesuai role
        if ($user->role === 'super_admin') {
            return redirect('/superadmin/dashboard');
        }

        return redirect('/admin/dashboard');
    }

    public function resendAdminOtp(Request $request)
    {
        $userId = session('admin_otp_user_id');

        if (!$userId) {
            return redirect()->route('admin.login')
                ->with('error', 'Sesi tidak valid. Silakan login kembali.');
        }

        // Cooldown 60 detik
        $lastSentAt = session('admin_otp_sent_at');
        if ($lastSentAt && (now()->timestamp - $lastSentAt) < 60) {
            $remaining = 60 - (now()->timestamp - $lastSentAt);
            return back()->with('error', "Tunggu {$remaining} detik sebelum kirim ulang.");
        }

        $user = User::findOrFail($userId);

        // Hapus OTP lama
        OtpCode::where('user_id', $userId)->where('is_used', false)->delete();

        // Generate OTP baru
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        OtpCode::create([
            'user_id'    => $userId,
            'code'       => $code,
            'expires_at' => now()->addMinutes(10),
            'is_used'    => false,
        ]);

        // Update timestamp
        $request->session()->put('admin_otp_sent_at', now()->timestamp);

        // Kirim email
        Mail::to($user->email)->queue(new OtpMail($code, $user->name));

        return back()->with('status', 'Kode OTP baru telah dikirim ke email kamu.');
    }
}