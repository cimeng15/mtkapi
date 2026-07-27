<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <style>
    :root{
        /* ---- Ruang Kontrol tokens ---- */
        --ink:#141A1E; --ink-soft:#5B6873; --line:#E4E8EC;
        --canvas:#EBEEF1; --surface:#FFFFFF; --surface-2:#F4F6F8;
        --brand:#0E6E64; --brand-strong:#0A544C; --brand-tint:#E7F1EF; --brand-border:#CADFDB;
        --gold:#B07A2E; --gold-tint:#F6EEDF;
        --online:#2E9E76; --danger:#C1554F; --warn:#C08A2D;
        --shadow-sm:0 1px 2px rgba(20,26,30,.04);
        --shadow:0 1px 2px rgba(20,26,30,.05), 0 14px 30px -20px rgba(20,26,30,.28);
        --r:14px; --r-sm:9px;
        --sidebar:#0E1719; --sidebar-2:#0A1113;

        /* ---- retheme Bootstrap so every screen inherits the palette ---- */
        --bs-body-bg:var(--canvas); --bs-body-color:var(--ink);
        --bs-border-color:var(--line);
        --bs-primary:#0E6E64; --bs-primary-rgb:14,110,100;
        --bs-primary-bg-subtle:#E7F1EF; --bs-primary-text-emphasis:#0A544C; --bs-primary-border-subtle:#CADFDB;
        --bs-success:#2E9E76; --bs-success-rgb:46,158,118;
        --bs-success-bg-subtle:#E4F3EC; --bs-success-text-emphasis:#1E7A58; --bs-success-border-subtle:#CFE9DC;
        --bs-warning:#C08A2D; --bs-warning-rgb:192,138,45;
        --bs-warning-bg-subtle:#F7EEDA; --bs-warning-text-emphasis:#8A6316; --bs-warning-border-subtle:#EBDCB9;
        --bs-danger:#C1554F; --bs-danger-rgb:193,85,79;
        --bs-danger-bg-subtle:#F8E7E5; --bs-danger-text-emphasis:#973B37; --bs-danger-border-subtle:#EFCFCC;
        --bs-info:#3C7F8C; --bs-info-rgb:60,127,140;
        --bs-info-bg-subtle:#E4F0F2; --bs-info-text-emphasis:#2A5C66; --bs-info-border-subtle:#C9E1E5;
        --bs-secondary:#5B6873; --bs-secondary-rgb:91,104,115;
        --bs-secondary-bg-subtle:#EDF0F2; --bs-secondary-text-emphasis:#404A53; --bs-secondary-border-subtle:#D8DDE1;
        --bs-link-color:#0E6E64; --bs-link-hover-color:#0A544C;
        --bs-font-sans-serif:'Inter',system-ui,sans-serif;
        --bs-border-radius:var(--r-sm); --bs-border-radius-lg:var(--r);
    }

    body{ background:var(--canvas); color:var(--ink); font-family:'Inter',system-ui,sans-serif; -webkit-font-smoothing:antialiased; }
    ::selection{ background:var(--brand-tint); }
    h1,h2,h3,h4,h5,h6,.h1,.h2,.h3,.h4,.h5{ font-family:'Space Grotesk','Inter',sans-serif; letter-spacing:-.01em; }
    code,kbd,samp,.mono{ font-family:'JetBrains Mono',ui-monospace,monospace; font-size:.82em; }
    code{ color:var(--brand-strong); background:var(--brand-tint); padding:.06em .42em; border-radius:6px; font-weight:500; }
    a{ text-decoration:none; }

    /* ================= SIDEBAR ================= */
    #sidebar{ width:248px; min-height:100vh; position:fixed; top:0; left:0; z-index:1030; transition:left .22s ease;
        background:linear-gradient(180deg,var(--sidebar),var(--sidebar-2)); color:#9FB0B2;
        border-right:1px solid rgba(255,255,255,.05); display:flex; flex-direction:column; }
    #sidebar .brand{ display:flex; align-items:center; gap:.7rem; padding:1.15rem 1.25rem; color:#fff; }
    #sidebar .brand .mark{ width:34px; height:34px; border-radius:9px; display:grid; place-items:center; font-size:1.05rem; color:#fff;
        background:linear-gradient(150deg,var(--brand),#0B5A52); box-shadow:0 4px 14px -4px rgba(14,110,100,.7); }
    #sidebar .brand .wm{ font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:1rem; letter-spacing:-.01em; line-height:1.05; }
    #sidebar .brand .wm small{ display:block; font-family:'Inter',sans-serif; font-weight:500; font-size:.62rem; letter-spacing:.16em; text-transform:uppercase; color:#5E7173; margin-top:2px; }
    #sidebar .nav{ padding:.35rem .6rem 1.5rem; overflow-y:auto; }
    #sidebar .nav-heading{ color:#4E6062; font-size:.66rem; font-weight:600; text-transform:uppercase; letter-spacing:.14em; padding:1.1rem .7rem .4rem; }
    #sidebar .nav-link{ position:relative; color:#9FB0B2; padding:.55rem .7rem; margin:1px 0; border-radius:8px; display:flex; align-items:center; gap:.7rem; font-size:.9rem; font-weight:500; transition:background .14s,color .14s; }
    #sidebar .nav-link i{ font-size:1.02rem; opacity:.85; width:1.1rem; text-align:center; }
    #sidebar .nav-link:hover{ background:rgba(255,255,255,.05); color:#EAF1F1; }
    #sidebar .nav-link.active{ background:rgba(14,110,100,.16); color:#EAF6F4; }
    #sidebar .nav-link.active i{ opacity:1; color:#39C7B4; }
    #sidebar .nav-link.active::before{ content:""; position:absolute; left:-.6rem; top:20%; bottom:20%; width:3px; border-radius:0 3px 3px 0; background:linear-gradient(180deg,#2FB9A7,var(--brand)); }

    /* ================= CONTENT / TOPBAR ================= */
    #content{ margin-left:248px; transition:margin .22s ease; min-height:100vh; display:flex; flex-direction:column; }
    .topbar{ position:sticky; top:0; z-index:1020; background:rgba(255,255,255,.86); backdrop-filter:saturate(1.4) blur(10px);
        border-bottom:1px solid var(--line); display:flex; align-items:center; gap:.75rem; padding:.7rem 1.35rem; min-height:60px; }
    .topbar .page-title{ font-family:'Space Grotesk',sans-serif; font-weight:600; font-size:1.16rem; color:var(--ink); letter-spacing:-.015em; margin:0; }
    .burger{ border:1px solid var(--line); background:var(--surface); width:38px; height:38px; border-radius:9px; display:grid; place-items:center; color:var(--ink); }

    /* router signal indicator — the signature */
    .router-pill{ display:inline-flex; align-items:center; gap:.5rem; padding:.4rem .7rem; border-radius:100px; font-size:.8rem; font-weight:500;
        border:1px solid var(--line); background:var(--surface); color:var(--ink-soft); }
    .router-pill .dot{ width:8px; height:8px; border-radius:50%; background:var(--ink-soft); flex:none; }
    .router-pill.is-on{ border-color:var(--brand-border); background:var(--brand-tint); color:var(--brand-strong); }
    .router-pill.is-on .dot{ background:var(--brand); } /* identitas router aktif, bukan klaim koneksi live */
    .router-pill .rp-host{ font-family:'JetBrains Mono',monospace; font-size:.76rem; }
    @keyframes signal{ 0%{ box-shadow:0 0 0 0 rgba(46,158,118,.55);} 70%{ box-shadow:0 0 0 7px rgba(46,158,118,0);} 100%{ box-shadow:0 0 0 0 rgba(46,158,118,0);} }

    .user-chip{ display:flex; align-items:center; gap:.6rem; padding:.28rem .5rem .28rem .32rem; border-radius:100px; border:1px solid transparent; color:var(--ink); transition:border-color .14s,background .14s; }
    .user-chip:hover{ border-color:var(--line); background:var(--surface-2); color:var(--ink); }
    .user-chip .avatar{ width:34px; height:34px; border-radius:50%; display:grid; place-items:center; font-family:'Space Grotesk',sans-serif; font-weight:600; font-size:.82rem; color:#fff; background:linear-gradient(150deg,var(--brand),var(--brand-strong)); }
    .user-chip .u-name{ font-weight:600; font-size:.84rem; line-height:1.05; }
    .user-chip .u-role{ font-size:.68rem; color:var(--ink-soft); text-transform:uppercase; letter-spacing:.08em; }

    main.app-main{ padding:1.5rem 1.35rem 3rem; flex:1; animation:rise .4s ease both; }
    @keyframes rise{ from{ opacity:0; transform:translateY(8px);} to{ opacity:1; transform:none; } }

    /* ================= COMPONENTS (reskin Bootstrap) ================= */
    .card{ border:1px solid var(--line); border-radius:var(--r); background:var(--surface); box-shadow:var(--shadow-sm); }
    .card-header{ background:transparent!important; border-bottom:1px solid var(--line); padding:.9rem 1.15rem; font-family:'Space Grotesk',sans-serif; font-weight:600; font-size:.92rem; letter-spacing:-.01em; }
    .card-body{ padding:1.15rem; }

    .btn{ font-weight:500; border-radius:var(--r-sm); padding:.5rem .95rem; letter-spacing:-.005em; transition:transform .04s ease, background .14s, box-shadow .14s; }
    .btn:active{ transform:translateY(1px); }
    .btn-sm{ padding:.34rem .6rem; border-radius:7px; }
    .btn-primary{ --bs-btn-bg:var(--brand); --bs-btn-border-color:var(--brand); --bs-btn-hover-bg:var(--brand-strong); --bs-btn-hover-border-color:var(--brand-strong); --bs-btn-active-bg:var(--brand-strong); --bs-btn-active-border-color:var(--brand-strong); --bs-btn-disabled-bg:var(--brand); --bs-btn-disabled-border-color:var(--brand); box-shadow:0 6px 16px -8px rgba(14,110,100,.6); }
    .btn-success{ --bs-btn-bg:var(--online); --bs-btn-border-color:var(--online); --bs-btn-hover-bg:#25845F; --bs-btn-hover-border-color:#25845F; --bs-btn-active-bg:#25845F; }
    .btn-danger{ --bs-btn-bg:var(--danger); --bs-btn-border-color:var(--danger); --bs-btn-hover-bg:#A6443F; --bs-btn-hover-border-color:#A6443F; }
    .btn-light{ --bs-btn-bg:var(--surface); --bs-btn-border-color:var(--line); --bs-btn-hover-bg:var(--surface-2); --bs-btn-hover-border-color:var(--line); }
    .btn-outline-primary{ --bs-btn-color:var(--brand); --bs-btn-border-color:var(--brand-border); --bs-btn-hover-bg:var(--brand); --bs-btn-hover-border-color:var(--brand); --bs-btn-active-bg:var(--brand); }
    .btn-outline-secondary{ --bs-btn-color:var(--ink-soft); --bs-btn-border-color:var(--line); --bs-btn-hover-bg:var(--surface-2); --bs-btn-hover-border-color:var(--ink-soft); --bs-btn-hover-color:var(--ink); }
    .btn-outline-success{ --bs-btn-color:var(--online); --bs-btn-border-color:var(--bs-success-border-subtle); --bs-btn-hover-bg:var(--online); --bs-btn-hover-border-color:var(--online); }
    .btn-outline-danger{ --bs-btn-color:var(--danger); --bs-btn-border-color:var(--bs-danger-border-subtle); --bs-btn-hover-bg:var(--danger); --bs-btn-hover-border-color:var(--danger); }
    .btn-outline-info{ --bs-btn-color:var(--bs-info); --bs-btn-border-color:var(--bs-info-border-subtle); --bs-btn-hover-bg:var(--bs-info); --bs-btn-hover-border-color:var(--bs-info); }

    .badge{ font-weight:500; letter-spacing:.01em; border-radius:100px; padding:.36em .7em; }
    .badge.bg-primary{ background:var(--brand)!important; }

    .form-control,.form-select{ border-color:var(--line); border-radius:var(--r-sm); padding:.55rem .8rem; background:var(--surface); color:var(--ink); font-size:.92rem; }
    .form-control::placeholder{ color:#9AA6B0; }
    .form-control:focus,.form-select:focus{ border-color:var(--brand); box-shadow:0 0 0 3px rgba(14,110,100,.14); }
    .form-control-sm,.form-select-sm{ border-radius:7px; }
    .form-label{ font-weight:500; font-size:.82rem; color:var(--ink-soft); margin-bottom:.35rem; }
    .form-check-input:checked{ background-color:var(--brand); border-color:var(--brand); }
    .form-check-input:focus{ border-color:var(--brand); box-shadow:0 0 0 3px rgba(14,110,100,.14); }
    .input-group-text{ background:var(--surface-2); border-color:var(--line); color:var(--ink-soft); }

    .table{ --bs-table-bg:transparent; color:var(--ink); margin-bottom:0; }
    .table > thead, .table-light > tr > th, thead.table-light th{ background:var(--surface-2); }
    .table thead th{ background:var(--surface-2); color:var(--ink-soft); font-size:.7rem; font-weight:600; text-transform:uppercase; letter-spacing:.06em; border-bottom:1px solid var(--line); padding:.7rem .9rem; }
    .table tbody td{ padding:.72rem .9rem; border-color:var(--line); vertical-align:middle; font-size:.9rem; }
    .table-hover > tbody > tr:hover > *{ background:var(--brand-tint); }
    .table-responsive{ border-radius:var(--r); }

    .alert{ border:1px solid var(--line); border-radius:var(--r); padding:.85rem 1.05rem; font-size:.9rem; box-shadow:var(--shadow-sm); }
    .alert-success{ border-color:var(--bs-success-border-subtle); }
    .alert-danger{ border-color:var(--bs-danger-border-subtle); }
    .alert-warning{ border-color:var(--bs-warning-border-subtle); }

    .dropdown-menu{ border:1px solid var(--line); border-radius:var(--r); box-shadow:var(--shadow); padding:.4rem; font-size:.9rem; }
    .dropdown-item{ border-radius:7px; padding:.5rem .7rem; }
    .dropdown-item:active{ background:var(--brand); }

    .page-link{ color:var(--brand); border-color:var(--line); border-radius:8px!important; margin:0 2px; }
    .page-item.active .page-link{ background:var(--brand); border-color:var(--brand); }
    .nav-tabs{ border-bottom:1px solid var(--line); }
    .nav-tabs .nav-link{ color:var(--ink-soft); border:none; border-bottom:2px solid transparent; border-radius:0; font-weight:500; }
    .nav-tabs .nav-link.active{ color:var(--brand); background:transparent; border-bottom-color:var(--brand); }

    .text-primary{ color:var(--brand)!important; }
    .text-muted{ color:var(--ink-soft)!important; }
    .bg-light{ background:var(--surface-2)!important; }
    hr{ border-color:var(--line); opacity:1; }

    /* ================= STAT READOUT (dashboard) ================= */
    .stat{ background:var(--surface); border:1px solid var(--line); border-radius:var(--r); padding:1.1rem 1.15rem; box-shadow:var(--shadow-sm); height:100%; }
    .stat .stat-label{ font-size:.72rem; font-weight:600; text-transform:uppercase; letter-spacing:.08em; color:var(--ink-soft); display:flex; align-items:center; gap:.4rem; }
    .stat .stat-value{ font-family:'Space Grotesk',sans-serif; font-weight:600; font-size:2.1rem; line-height:1.1; margin-top:.35rem; letter-spacing:-.02em; }
    .stat .stat-foot{ font-size:.76rem; color:var(--ink-soft); margin-top:.15rem; }
    .stat.is-live .stat-value{ color:var(--brand-strong); }
    .live-dot{ width:8px; height:8px; border-radius:50%; background:var(--online); display:inline-block; box-shadow:0 0 0 0 rgba(46,158,118,.5); animation:signal 2.4s ease-out infinite; }

    /* ================= RESPONSIVE ================= */
    @media (max-width:991.98px){
        #sidebar{ left:-260px; box-shadow:0 0 60px rgba(0,0,0,.3); }
        #sidebar.show{ left:0; }
        #content{ margin-left:0; }
        .topbar .router-pill .rp-host{ display:none; }
        main.app-main{ padding:1.15rem 1rem 3rem; }
    }
    .scrim{ position:fixed; inset:0; background:rgba(10,17,19,.4); z-index:1029; opacity:0; visibility:hidden; transition:.2s; }
    .scrim.show{ opacity:1; visibility:visible; }

    @media (prefers-reduced-motion:reduce){ *,*::before,*::after{ animation-duration:.001ms!important; transition-duration:.001ms!important; } }
    </style>
    @stack('head')
</head>
<body>
    @php $u = auth()->user(); $nr = $navRouter ?? null; @endphp

    <nav id="sidebar">
        <div class="brand">
            <span class="mark"><i class="bi bi-broadcast-pin"></i></span>
            <span class="wm">Hotspot<small>Kontrol Sekolah</small></span>
        </div>
        <ul class="nav flex-column">
            <li><a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="bi bi-grid-1x2"></i> Dashboard</a></li>
            <li><a class="nav-link {{ request()->routeIs('hotspot.monitor') ? 'active' : '' }}" href="{{ route('hotspot.monitor') }}"><i class="bi bi-broadcast"></i> Monitoring Sesi</a></li>

            <div class="nav-heading">Manajemen</div>
            <li><a class="nav-link {{ request()->routeIs('students.*') ? 'active' : '' }}" href="{{ route('students.index') }}"><i class="bi bi-mortarboard"></i> Data Siswa</a></li>
            <li><a class="nav-link {{ request()->routeIs('teachers.*') ? 'active' : '' }}" href="{{ route('teachers.index') }}"><i class="bi bi-person-badge"></i> Data Guru &amp; Tendik</a></li>
            <li><a class="nav-link {{ request()->routeIs('hotspot.index') ? 'active' : '' }}" href="{{ route('hotspot.index') }}"><i class="bi bi-person-vcard"></i> User Hotspot</a></li>
            <li><a class="nav-link {{ request()->routeIs('vouchers.*') ? 'active' : '' }}" href="{{ route('vouchers.index') }}"><i class="bi bi-ticket-perforated"></i> Voucher</a></li>
            <li><a class="nav-link {{ request()->routeIs('packages.*') ? 'active' : '' }}" href="{{ route('packages.index') }}"><i class="bi bi-box-seam"></i> Paket / Profil</a></li>

            <div class="nav-heading">Laporan</div>
            <li><a class="nav-link {{ request()->routeIs('reports.index') ? 'active' : '' }}" href="{{ route('reports.index') }}"><i class="bi bi-bar-chart"></i> Laporan</a></li>
            <li><a class="nav-link {{ request()->routeIs('reports.logs') ? 'active' : '' }}" href="{{ route('reports.logs') }}"> Log Aktivitas</a></li>

            @if($u && $u->isSuperadmin())
            <div class="nav-heading">Sistem</div>
            <li><a class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.mikrotik.edit') }}"><i class="bi bi-router"></i> Pengaturan Router</a></li>
            <li><a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}"><i class="bi bi-shield-lock"></i> Kelola Admin</a></li>
            @endif
        </ul>
    </nav>

    <div class="scrim" id="scrim" onclick="toggleNav(false)"></div>

    <div id="content">
        <header class="topbar">
            <button class="burger d-lg-none" onclick="toggleNav()"><i class="bi bi-list"></i></button>
            <h1 class="page-title">@yield('title', 'Dashboard')</h1>

            <div class="ms-auto d-flex align-items-center gap-2 gap-md-3">
                @if($nr)
                    <span class="router-pill is-on" title="Router aktif: {{ $nr->name }} ({{ $nr->host }}:{{ $nr->port }})">
                        <span class="dot"></span><span class="d-none d-sm-inline">Router</span><span class="rp-host">{{ $nr->host }}</span>
                    </span>
                @else
                    <a href="{{ $u && $u->isSuperadmin() ? route('settings.mikrotik.edit') : '#' }}" class="router-pill" title="Router belum dikonfigurasi">
                        <span class="dot"></span><span>Router belum diatur</span>
                    </a>
                @endif

                <div class="dropdown">
                    <a href="#" class="user-chip dropdown-toggle" data-bs-toggle="dropdown" style="text-decoration:none">
                        <span class="avatar">{{ strtoupper(mb_substr($u?->name ?? 'A',0,1)) }}{{ strtoupper(mb_substr(strstr($u?->name.' ',' '),1,1)) }}</span>
                        <span class="text-start d-none d-sm-block">
                            <span class="u-name d-block">{{ $u?->name }}</span>
                            <span class="u-role">{{ ucfirst($u?->role) }}</span>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person-gear me-2"></i>Profil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">@csrf
                                <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Keluar</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <main class="app-main">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    <button class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('modals')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleNav(force){
            const sb=document.getElementById('sidebar'), sc=document.getElementById('scrim');
            const show = force===undefined ? !sb.classList.contains('show') : force;
            sb.classList.toggle('show', show); sc.classList.toggle('show', show);
        }
    </script>
    @stack('scripts')
</body>
</html>
