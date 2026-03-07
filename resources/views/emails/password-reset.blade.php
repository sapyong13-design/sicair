<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0fdf4; margin: 0; padding: 2rem; }
        .container { max-width: 520px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 16px rgba(0,0,0,0.08); }
        .header { background: #166534; padding: 1.5rem 2rem; text-align: center; }
        .header h1 { color: white; margin: 0; font-size: 1.25rem; font-weight: 700; }
        .body { padding: 2rem; }
        .btn { display: inline-block; background: #166534; color: white !important; padding: 0.75rem 2rem; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 1rem; }
        .footer { background: #f8fafc; padding: 1rem 2rem; text-align: center; color: #94a3b8; font-size: 0.8rem; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🔐 Reset Password SiHEALING</h1>
    </div>
    <div class="body">
        <p>Halo, <strong>{{ $user->name }}</strong>.</p>
        <p>Kami menerima permintaan reset password untuk akun Anda di <strong>SiHEALING — Pengadilan Negeri Natuna</strong>.</p>
        <p>Klik tombol di bawah untuk membuat password baru:</p>
        <div style="text-align: center; margin: 2rem 0;">
            <a href="{{ $url }}" class="btn">Reset Password Saya</a>
        </div>
        <p style="color: #64748b; font-size: 0.875rem;">
            ⚠️ Link ini akan kadaluarsa dalam <strong>60 menit</strong>.<br>
            Jika Anda tidak meminta reset password, abaikan email ini — akun Anda tetap aman.
        </p>
        <p style="color: #64748b; font-size: 0.875rem;">
            Jika tombol tidak berfungsi, salin URL berikut ke browser:<br>
            <span style="word-break: break-all; color: #166534;">{{ $url }}</span>
        </p>
    </div>
    <div class="footer">
        SiHEALING — Sistem Informasi Hak Elektronik Cuti<br>
        Pengadilan Negeri Natuna
    </div>
</div>
</body>
</html>
