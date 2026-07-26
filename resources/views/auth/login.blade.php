<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <style>
    :root{ --ink:#141A1E; --ink-soft:#5B6873; --line:#E4E8EC; --surface:#fff; --surface-2:#F4F6F8;
        --brand:#0E6E64; --brand-strong:#0A544C; --brand-tint:#E7F1EF; --online:#2E9E76; --sidebar:#0E1719; }
    *{ box-sizing:border-box; }
    body{ margin:0; min-height:100vh; font-family:'Inter',system-ui,sans-serif; color:var(--ink); background:var(--surface-2); display:grid; grid-template-columns:1.05fr .95fr; }
    h1,h2,h3{ font-family:'Space Grotesk',sans-serif; letter-spacing:-.02em; }

    /* left: control-room panel */
    .stage{ position:relative; overflow:hidden; padding:3.5rem; color:#CFDCDB; display:flex; flex-direction:column; justify-content:space-between;
        background:radial-gradient(120% 90% at 15% 0%, #15302C 0%, var(--sidebar) 55%, #080E10 100%); }
    .stage::after{ content:""; position:absolute; inset:0; opacity:.5;
        background-image:linear-gradient(rgba(255,255,255,.035) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.035) 1px,transparent 1px);
        background-size:44px 44px; -webkit-mask-image:radial-gradient(80% 70% at 30% 20%,#000,transparent 75%); mask-image:radial-gradient(80% 70% at 30% 20%,#000,transparent 75%); }
    .stage > *{ position:relative; z-index:1; }
    .brand{ display:flex; align-items:center; gap:.75rem; color:#fff; }
    .brand .mark{ width:44px; height:44px; border-radius:12px; display:grid; place-items:center; font-size:1.35rem; color:#fff;
        background:linear-gradient(150deg,var(--brand),#0B5A52); box-shadow:0 8px 24px -8px rgba(14,110,100,.8); }
    .brand .wm{ font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:1.15rem; line-height:1; }
    .brand .wm small{ display:block; font-size:.64rem; font-weight:500; letter-spacing:.18em; text-transform:uppercase; color:#6E8280; margin-top:4px; }
    .stage h1{ color:#fff; font-size:2.5rem; line-height:1.08; margin:0 0 1rem; max-width:15ch; }
    .stage .lede{ color:#9FB0B2; font-size:1.02rem; max-width:34ch; line-height:1.6; }
    .signals{ display:flex; gap:1.75rem; flex-wrap:wrap; }
    .signals .s{ display:flex; align-items:center; gap:.55rem; font-size:.82rem; color:#9FB0B2; }
    .signals .s .dot{ width:8px; height:8px; border-radius:50%; background:var(--online); box-shadow:0 0 0 0 rgba(46,158,118,.5); animation:sig 2.4s ease-out infinite; }
    .signals .s .mono{ font-family:'JetBrains Mono',monospace; color:#CFDCDB; }
    @keyframes sig{ 0%{box-shadow:0 0 0 0 rgba(46,158,118,.5)} 70%{box-shadow:0 0 0 8px rgba(46,158,118,0)} 100%{box-shadow:0 0 0 0 rgba(46,158,118,0)} }

    /* right: form */
    .panel{ display:flex; align-items:center; justify-content:center; padding:2.5rem; }
    .form-wrap{ width:100%; max-width:380px; }
    .form-wrap .eyebrow{ font-size:.72rem; font-weight:600; letter-spacing:.16em; text-transform:uppercase; color:var(--brand); }
    .form-wrap h2{ font-size:1.7rem; margin:.35rem 0 .35rem; }
    .form-wrap .sub{ color:var(--ink-soft); font-size:.92rem; margin-bottom:1.75rem; }
    .form-label{ font-weight:500; font-size:.82rem; color:var(--ink-soft); margin-bottom:.35rem; }
    .form-control{ border-color:var(--line); border-radius:10px; padding:.7rem .9rem; font-size:.95rem; }
    .form-control:focus{ border-color:var(--brand); box-shadow:0 0 0 3px rgba(14,110,100,.14); }
    .input-ico{ position:relative; }
    .input-ico i{ position:absolute; left:.85rem; top:50%; transform:translateY(-50%); color:#9AA6B0; }
    .input-ico .form-control{ padding-left:2.5rem; }
    .btn-brand{ background:var(--brand); border:none; color:#fff; border-radius:10px; padding:.72rem 1rem; font-weight:600; width:100%;
        box-shadow:0 10px 24px -10px rgba(14,110,100,.7); transition:background .14s, transform .04s; }
    .btn-brand:hover{ background:var(--brand-strong); color:#fff; }
    .btn-brand:active{ transform:translateY(1px); }
    .form-check-input:checked{ background-color:var(--brand); border-color:var(--brand); }
    .form-check-input:focus{ border-color:var(--brand); box-shadow:0 0 0 3px rgba(14,110,100,.14); }
    a.link{ color:var(--brand); font-size:.85rem; text-decoration:none; font-weight:500; }
    a.link:hover{ text-decoration:underline; }
    .err{ background:#F8E7E5; border:1px solid #EFCFCC; color:#973B37; border-radius:10px; padding:.7rem .9rem; font-size:.85rem; margin-bottom:1.1rem; }
    .status-ok{ background:#E4F3EC; border:1px solid #CFE9DC; color:#1E7A58; border-radius:10px; padding:.7rem .9rem; font-size:.85rem; margin-bottom:1.1rem; }

    @media (max-width:860px){ body{ grid-template-columns:1fr; } .stage{ display:none; } .panel{ padding:1.5rem; } }
    @media (prefers-reduced-motion:reduce){ *{ animation-duration:.001ms!important; } }
    </style>
</head>
<body>
    <section class="stage">
        <div class="brand">
            <span class="mark"><i class="bi bi-broadcast-pin"></i></span>
            <span class="wm">Hotspot<small>Kontrol Sekolah</small></span>
        </div>
        <div>
            <h1>Kendali penuh atas akses internet sekolah.</h1>
            <p class="lede">Kelola akun siswa &amp; guru, paket kecepatan, voucher, dan pantau siapa yang online — semua terhubung langsung ke MikroTik.</p>
        </div>
        <div class="signals">
            <span class="s"><span class="dot"></span> Terhubung ke RouterOS</span>
            <span class="s"><i class="bi bi-shield-check"></i> Sesi aman &amp; terenkripsi</span>
        </div>
    </section>

    <section class="panel">
        <div class="form-wrap">
            <div class="eyebrow">Panel Operator</div>
            <h2>Selamat datang kembali</h2>
            <p class="sub">Masuk untuk mengelola jaringan hotspot sekolah.</p>

            @if(session('status'))
                <div class="status-ok">{{ session('status') }}</div>
            @endif
            @if($errors->any())
                <div class="err"><i class="bi bi-exclamation-circle me-1"></i>{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <div class="input-ico">
                        <i class="bi bi-envelope"></i>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus autocomplete="username" placeholder="admin@sekolah.id">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Kata Sandi</label>
                    <div class="input-ico">
                        <i class="bi bi-lock"></i>
                        <input id="password" type="password" name="password" class="form-control" required autocomplete="current-password" placeholder="••••••••">
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label" for="remember" style="font-size:.85rem;color:var(--ink-soft)">Ingat saya</label>
                    </div>
                    @if(Route::has('password.request'))
                        <a class="link" href="{{ route('password.request') }}">Lupa sandi?</a>
                    @endif
                </div>
                <button type="submit" class="btn-brand"><i class="bi bi-box-arrow-in-right me-1"></i> Masuk</button>
            </form>
        </div>
    </section>
</body>
</html>
