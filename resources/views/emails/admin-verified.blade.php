<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Akun Diverifikasi</title>
</head>
<body style="font-family: 'Plus Jakarta Sans', Arial, sans-serif; background-color: #f8fafc; padding: 20px; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <h2 style="color: #10B981; text-align: center;">Selamat Datang di Tim EcoDrop! 🌿</h2>
        
        <p>Halo <strong>{{ $adminName }}</strong>,</p>
        
        <p>Kabar gembira! Akun Admin Anda baru saja selesai diverifikasi oleh Super Admin.</p>
        
        <p>Sekarang Anda sudah memiliki akses penuh ke Dashboard Admin dan dapat mulai membantu menangani setoran sampah dari para pengguna EcoDrop.</p>
        
        <div style="text-align: center; margin-top: 30px; margin-bottom: 30px;">
            <a href="{{ url('/admin/login') }}" style="background-color: #10B981; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">Login ke Dashboard Admin</a>
        </div>
        
        <p>Jika tombol di atas tidak berfungsi, silakan copy dan paste link berikut di browser Anda:<br>
        <a href="{{ url('/admin/login') }}" style="color: #10B981;">{{ url('/admin/login') }}</a></p>
        
        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 20px 0;">
        
        <p style="font-size: 12px; color: #6b7280; text-align: center;">
            Email ini dikirim secara otomatis oleh sistem EcoDrop.<br>
            Mohon tidak membalas email ini.
        </p>
    </div>
</body>
</html>