<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kode OTP EcoDrop</title>
</head>
<body style="margin:0;padding:0;background:#f0fdf4;font-family:'Segoe UI',Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f0fdf4;padding:40px 20px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width:500px;background:#ffffff;border-radius:24px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

                    {{-- Header --}}
                    <tr>
                        <td style="background:linear-gradient(135deg,#10b981,#059669);padding:40px 40px 32px;text-align:center;">
                            <div style="font-size:48px;margin-bottom:12px;">🌱</div>
                            <h1 style="margin:0;color:#ffffff;font-size:28px;font-weight:900;letter-spacing:-0.5px;">EcoDrop</h1>
                            <p style="margin:8px 0 0;color:#a7f3d0;font-size:14px;">Platform Manajemen Sampah Berbasis Reward</p>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding:40px;">
                            <p style="margin:0 0 8px;color:#6b7280;font-size:14px;">Halo,</p>
                            <h2 style="margin:0 0 20px;color:#111827;font-size:22px;font-weight:800;">{{ $userName }} 👋</h2>

                            <p style="margin:0 0 24px;color:#374151;font-size:15px;line-height:1.6;">
                                Kamu baru saja mencoba login ke akun EcoDrop. Gunakan kode OTP berikut untuk melanjutkan:
                            </p>

                            {{-- OTP Box --}}
                            <div style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:2px solid #86efac;border-radius:16px;padding:32px;text-align:center;margin:0 0 24px;">
                                <p style="margin:0 0 8px;color:#16a34a;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:2px;">Kode OTP Anda</p>
                                <div style="font-size:48px;font-weight:900;letter-spacing:12px;color:#15803d;font-family:'Courier New',monospace;">{{ $otp }}</div>
                                <p style="margin:12px 0 0;color:#6b7280;font-size:13px;">⏱️ Berlaku selama <strong>5 menit</strong></p>
                            </div>

                            <div style="background:#fef3c7;border:1px solid #fcd34d;border-radius:12px;padding:16px;margin:0 0 24px;">
                                <p style="margin:0;color:#92400e;font-size:13px;line-height:1.5;">
                                    ⚠️ <strong>Jangan bagikan kode ini</strong> kepada siapapun, termasuk tim EcoDrop. Kami tidak pernah meminta kode OTP kamu.
                                </p>
                            </div>

                            <p style="margin:0;color:#9ca3af;font-size:13px;line-height:1.5;">
                                Jika kamu tidak mencoba login, abaikan email ini. Akun kamu tetap aman.
                            </p>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background:#f9fafb;border-top:1px solid #f3f4f6;padding:24px 40px;text-align:center;">
                            <p style="margin:0;color:#9ca3af;font-size:12px;">© 2026 EcoDrop. Semua hak dilindungi.</p>
                            <p style="margin:4px 0 0;color:#9ca3af;font-size:12px;">Dibuat dengan ❤️ untuk planet yang lebih baik</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>