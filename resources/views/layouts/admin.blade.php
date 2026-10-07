<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0E1719">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <style>
    :root{
        --ink:#141A1E; --ink-soft:#5B6873; --line:#E4E8EC;
        --canvas:#EBEEF1; --surface:#FFFFFF; --surface-2:#F4F6F8;
        --brand:#0E6E64; --brand-strong:#0A544C; --brand-tint:#E7F1EF; --brand-border:#CADFDB;
        --gold:#B07A2E; --gold-tint:#F6EEDF;
        --online:#2E9E76; --danger:#C1554F; --warn:#C08A2D;
        --shadow-sm:0 1px 2px rgba(20,26,30,.04);
        --shadow:0 1px 2px rgba(20,26,30,.05), 0 14px 30px -20px rgba(20,26,30,.28);
        --r:14px; --r-sm:9px;
        --sidebar:#0E1719; --sidebar-2:#0A1113;
        --bnav-h:64px; /* bottom nav height */
        --safe-b:env(safe-area-inset-bottom, 0px);

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

    /* ================= SIDEBAR (desktop ≥992px) ================= */
    #sidebar{ width:248px; min-height:100vh; position:fixed; top:0; left:0; z-index:1030;
        transition:width .28s cubic-bezier(.4,0,.2,1), left .22s ease;
        background:linear-gradient(180deg,var(--sidebar),var(--sidebar-2)); color:#9FB0B2;
        border-right:1px solid rgba(255,255,255,.05); display:flex; flex-direction:column; overflow:hidden; }
    #sidebar .brand{ display:flex; align-items:center; gap:.7rem; padding:1.15rem 1.25rem; color:#fff; white-space:nowrap; }
    #sidebar .brand .mark{ width:34px; height:34px; min-width:34px; border-radius:9px; display:grid; place-items:center; font-size:1.05rem; color:#fff;
        background:linear-gradient(150deg,var(--brand),#0B5A52); box-shadow:0 4px 14px -4px rgba(14,110,100,.7); }
    #sidebar .brand .wm{ font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:1rem; letter-spacing:-.01em; line-height:1.05;
        opacity:1; transition:opacity .18s; overflow:hidden; }
    #sidebar .brand .wm small{ display:block; font-family:'Inter',sans-serif; font-weight:500; font-size:.62rem; letter-spacing:.16em; text-transform:uppercase; color:#5E7173; margin-top:2px; }
    #sidebar .nav{ padding:.35rem .6rem 1.5rem; overflow-y:auto; overflow-x:hidden; flex:1; }
    #sidebar .nav-heading{ color:#4E6062; font-size:.66rem; font-weight:600; text-transform:uppercase; letter-spacing:.14em; padding:1.1rem .7rem .4rem;
        white-space:nowrap; opacity:1; transition:opacity .18s; overflow:hidden; }
    #sidebar .nav-link{ position:relative; color:#9FB0B2; padding:.55rem .7rem; margin:1px 0; border-radius:8px; display:flex; align-items:center; gap:.7rem; font-size:.9rem; font-weight:500;
        transition:background .14s,color .14s,padding .28s; white-space:nowrap; overflow:hidden; }
    #sidebar .nav-link i{ font-size:1.02rem; opacity:.85; width:1.1rem; min-width:1.1rem; text-align:center; transition:transform .15s; }
    #sidebar .nav-link:hover{ background:rgba(255,255,255,.05); color:#EAF1F1; }
    #sidebar .nav-link:hover i{ transform:scale(1.12); }
    #sidebar .nav-link.active{ background:rgba(14,110,100,.16); color:#EAF6F4; }
    #sidebar .nav-link.active i{ opacity:1; color:#39C7B4; }
    #sidebar .nav-link.active::before{ content:""; position:absolute; left:-.6rem; top:20%; bottom:20%; width:3px; border-radius:0 3px 3px 0; background:linear-gradient(180deg,#2FB9A7,var(--brand)); }
    /* nav-link text label (for collapse/expand) */
    #sidebar .nav-link .lk-text{ opacity:1; transition:opacity .18s; }

    /* Sidebar collapse toggle button */
    #sidebar .sb-toggle{ display:flex; align-items:center; justify-content:center; gap:.5rem; padding:.65rem; margin:.4rem .6rem .8rem;
        border-radius:8px; border:1px solid rgba(255,255,255,.08); background:rgba(255,255,255,.03);
        color:#6B8183; font-size:.76rem; font-weight:500; cursor:pointer; white-space:nowrap; overflow:hidden;
        transition:background .14s, border-color .14s; }
    #sidebar .sb-toggle:hover{ background:rgba(255,255,255,.06); border-color:rgba(255,255,255,.14); color:#9FB0B2; }
    #sidebar .sb-toggle i{ font-size:.9rem; transition:transform .28s; min-width:1rem; text-align:center; }
    #sidebar .sb-toggle .tog-text{ opacity:1; transition:opacity .18s; }

    /* Sidebar user card at bottom */
    #sidebar .sb-user{ display:flex; align-items:center; gap:.6rem; padding:.75rem 1rem; border-top:1px solid rgba(255,255,255,.06);
        white-space:nowrap; overflow:hidden; text-decoration:none; transition:background .14s; }
    #sidebar .sb-user:hover{ background:rgba(255,255,255,.04); }
    #sidebar .sb-user .sb-avatar{ width:32px; height:32px; min-width:32px; border-radius:50%; display:grid; place-items:center;
        font-family:'Space Grotesk',sans-serif; font-weight:600; font-size:.72rem; color:#fff;
        background:linear-gradient(150deg,var(--brand),var(--brand-strong)); }
    #sidebar .sb-user .sb-uinfo{ opacity:1; transition:opacity .18s; overflow:hidden; }
    #sidebar .sb-user .sb-uname{ font-size:.82rem; font-weight:600; color:#D0DBDD; line-height:1.1; }
    #sidebar .sb-user .sb-urole{ font-size:.64rem; color:#5E7173; text-transform:uppercase; letter-spacing:.06em; }

    /* ---- Collapsed sidebar (mini mode) ---- */
    #sidebar.mini{ width:68px; }
    #sidebar.mini .brand{ padding:1.15rem .9rem; justify-content:center; }
    #sidebar.mini .brand .wm{ opacity:0; width:0; }
    #sidebar.mini .nav{ padding:.35rem .45rem 1.5rem; }
    #sidebar.mini .nav-heading{ opacity:0; height:0; padding:0; margin:0; pointer-events:none; }
    #sidebar.mini .nav-link{ padding:.55rem 0; justify-content:center; border-radius:10px; }
    #sidebar.mini .nav-link .lk-text{ opacity:0; width:0; overflow:hidden; }
    #sidebar.mini .nav-link.active::before{ left:-.45rem; }
    #sidebar.mini .sb-toggle{ justify-content:center; }
    #sidebar.mini .sb-toggle i{ transform:rotate(180deg); }
    #sidebar.mini .sb-toggle .tog-text{ opacity:0; width:0; }
    #sidebar.mini .sb-user{ justify-content:center; padding:.75rem .5rem; }
    #sidebar.mini .sb-user .sb-uinfo{ opacity:0; width:0; }

    /* Tooltip for collapsed sidebar */
    #sidebar.mini .nav-link{ position:relative; }
    #sidebar.mini .nav-link::after{ content:attr(data-tip); position:absolute; left:calc(100% + 8px); top:50%; transform:translateY(-50%);
        background:var(--sidebar); color:#EAF1F1; padding:.32rem .6rem; border-radius:6px; font-size:.78rem; font-weight:500;
        white-space:nowrap; pointer-events:none; opacity:0; transition:opacity .15s; z-index:9999;
        box-shadow:0 4px 16px rgba(0,0,0,.3); border:1px solid rgba(255,255,255,.08); }
    #sidebar.mini .nav-link:hover::after{ opacity:1; }

    /* ================= CONTENT / TOPBAR ================= */
    #content{ margin-left:248px; transition:margin .28s cubic-bezier(.4,0,.2,1); min-height:100vh; display:flex; flex-direction:column; }
    body.sb-mini #content{ margin-left:68px; }
    .topbar{ position:sticky; top:0; z-index:1020; background:rgba(255,255,255,.86); backdrop-filter:saturate(1.4) blur(10px);
        border-bottom:1px solid var(--line); display:flex; align-items:center; gap:.75rem; padding:.7rem 1.35rem; min-height:60px; }
    .topbar .page-title{ font-family:'Space Grotesk',sans-serif; font-weight:600; font-size:1.16rem; color:var(--ink); letter-spacing:-.015em; margin:0; }
    .burger{ border:1px solid var(--line); background:var(--surface); width:38px; height:38px; border-radius:9px; display:grid; place-items:center; color:var(--ink); }
    /* Default: mobile burger visible, desktop burger hidden. Reversed by is-desktop. */
    .burger-desktop{ display:none; }
    html.is-desktop .burger-mobile{ display:none!important; }
    html.is-desktop .burger-desktop{ display:grid; }
    @media (min-width:992px){
        .burger-mobile{ display:none!important; }
        .burger-desktop{ display:grid; }
    }

    .router-pill{ display:inline-flex; align-items:center; gap:.5rem; padding:.4rem .7rem; border-radius:100px; font-size:.8rem; font-weight:500;
        border:1px solid var(--line); background:var(--surface); color:var(--ink-soft); }
    .router-pill .dot{ width:8px; height:8px; border-radius:50%; background:var(--ink-soft); flex:none; }
    .router-pill.is-on{ border-color:var(--brand-border); background:var(--brand-tint); color:var(--brand-strong); }
    .router-pill.is-on .dot{ background:var(--brand); }
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

    /* ================= BOTTOM NAV (mobile <992px) ================= */
    #bottomNav{
        display:none; /* hidden on desktop */
        position:fixed; bottom:0; left:0; right:0; z-index:1040;
        background:var(--surface); border-top:1px solid var(--line);
        padding-bottom:var(--safe-b);
        box-shadow:0 -2px 20px rgba(20,26,30,.08);
    }
    #bottomNav .bnav-inner{
        display:flex; justify-content:space-around; align-items:stretch;
        height:var(--bnav-h); max-width:600px; margin:0 auto;
    }
    #bottomNav .bnav-item{
        flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center;
        color:var(--ink-soft); text-decoration:none; font-size:.62rem; font-weight:600;
        letter-spacing:.02em; gap:2px; position:relative;
        transition:color .15s; -webkit-tap-highlight-color:transparent;
        padding:4px 0;
    }
    #bottomNav .bnav-item i{ font-size:1.25rem; transition:transform .15s; }
    #bottomNav .bnav-item.active{ color:var(--brand); }
    #bottomNav .bnav-item.active i{ transform:scale(1.08); }
    #bottomNav .bnav-item.active::after{
        content:""; position:absolute; top:0; left:25%; right:25%; height:2.5px;
        border-radius:0 0 3px 3px; background:var(--brand);
    }
    #bottomNav .bnav-item:active i{ transform:scale(.9); }
    /* live badge on bnav */
    #bottomNav .bnav-item .bnav-badge{
        position:absolute; top:6px; right:calc(50% - 18px);
        min-width:16px; height:16px; border-radius:100px; padding:0 4px;
        background:var(--online); color:#fff; font-size:.56rem; font-weight:700;
        display:flex; align-items:center; justify-content:center;
        box-shadow:0 0 0 2px var(--surface);
    }

    /* ---- "More" bottom sheet ---- */
    #moreSheet{
        position:fixed; bottom:0; left:0; right:0; z-index:1050;
        background:var(--surface); border-radius:20px 20px 0 0;
        box-shadow:0 -8px 40px rgba(10,17,19,.18);
        transform:translateY(100%); transition:transform .28s cubic-bezier(.4,.0,.2,1);
        padding-bottom:var(--safe-b);
        max-height:80vh; overflow-y:auto;
    }
    #moreSheet.open{ transform:translateY(0); }
    #moreSheet .sheet-handle{
        width:36px; height:4px; border-radius:4px; background:var(--line);
        margin:10px auto 6px;
    }
    #moreSheet .sheet-grid{
        display:grid; grid-template-columns:repeat(4,1fr); gap:4px;
        padding:8px 12px 16px;
    }
    #moreSheet .sheet-item{
        display:flex; flex-direction:column; align-items:center; justify-content:center;
        padding:14px 4px 10px; border-radius:var(--r); color:var(--ink);
        text-decoration:none; font-size:.72rem; font-weight:500; gap:6px;
        transition:background .12s; -webkit-tap-highlight-color:transparent;
    }
    #moreSheet .sheet-item:active{ background:var(--brand-tint); }
    #moreSheet .sheet-item i{ font-size:1.4rem; color:var(--brand); }
    #moreSheet .sheet-item.active{ background:var(--brand-tint); color:var(--brand-strong); font-weight:600; }
    #moreSheet .sheet-section{
        font-size:.64rem; font-weight:600; text-transform:uppercase; letter-spacing:.1em;
        color:var(--ink-soft); padding:12px 20px 4px;
    }
    .sheet-scrim{
        position:fixed; inset:0; z-index:1045; background:rgba(10,17,19,.35);
        opacity:0; visibility:hidden; transition:opacity .2s, visibility .2s;
    }
    .sheet-scrim.open{ opacity:1; visibility:visible; }

    /* ================= MOBILE TOPBAR (mobile) ================= */
    @media (max-width:991.98px){
        html:not(.is-desktop) #sidebar{ left:-260px; box-shadow:0 0 60px rgba(0,0,0,.3); }
        html:not(.is-desktop) #sidebar.show{ left:0; }
        html:not(.is-desktop) #content{ margin-left:0; }
        html:not(.is-desktop) #bottomNav{ display:block; }

        html:not(.is-desktop) .topbar{
            padding:.55rem .9rem; min-height:52px; gap:.5rem;
            border-bottom:none; background:var(--surface);
            box-shadow:0 1px 3px rgba(20,26,30,.06);
        }
        html:not(.is-desktop) .topbar .page-title{ font-size:1.02rem; }
        html:not(.is-desktop) .topbar .router-pill{ padding:.3rem .55rem; font-size:.72rem; }
        html:not(.is-desktop) .topbar .router-pill .rp-host{ display:none; }
        html:not(.is-desktop) .topbar .user-chip .u-name,
        html:not(.is-desktop) .topbar .user-chip .u-role{ display:none; }
        html:not(.is-desktop) .topbar .user-chip .avatar{ width:30px; height:30px; font-size:.72rem; }

        html:not(.is-desktop) main.app-main{
            padding:.85rem .75rem calc(var(--bnav-h) + var(--safe-b) + 1rem);
        }

        /* Mobile stat cards: compact */
        html:not(.is-desktop) .stat{ padding:.8rem .9rem; }
        html:not(.is-desktop) .stat .stat-value{ font-size:1.55rem; }
        html:not(.is-desktop) .stat .stat-label{ font-size:.64rem; }
        html:not(.is-desktop) .stat .stat-foot{ font-size:.68rem; }

        /* Mobile card tweaks */
        html:not(.is-desktop) .card{ border-radius:var(--r-sm); }
        html:not(.is-desktop) .card-header{ padding:.7rem .9rem; font-size:.85rem; }
        html:not(.is-desktop) .card-body{ padding:.9rem; }

        /* Table mobile: tighter */
        html:not(.is-desktop) .table thead th{ padding:.5rem .6rem; font-size:.62rem; }
        html:not(.is-desktop) .table tbody td{ padding:.55rem .6rem; font-size:.82rem; }

        /* Mobile uses bottom nav — hide mobile burger since bottom nav covers it */
        html:not(.is-desktop) .burger-mobile{ display:none!important; }
    }

    .scrim{ position:fixed; inset:0; background:rgba(10,17,19,.4); z-index:1029; opacity:0; visibility:hidden; transition:.2s; }
    .scrim.show{ opacity:1; visibility:visible; }

    /* ================= PULL-TO-REFRESH indicator ================= */
    #pullIndicator{
        position:fixed; top:0; left:50%; transform:translateX(-50%) translateY(-50px);
        z-index:9999; transition:transform .25s ease;
        width:40px; height:40px; border-radius:50%;
        background:var(--surface); box-shadow:var(--shadow);
        display:grid; place-items:center;
    }
    #pullIndicator.pulling{ transform:translateX(-50%) translateY(16px); }
    #pullIndicator .spinner-border{ width:20px; height:20px; border-width:2px; color:var(--brand); }

    /* ================= SWIPE HINT (first visit) ================= */
    .swipe-toast{
        position:fixed; bottom:calc(var(--bnav-h) + var(--safe-b) + 12px);
        left:50%; transform:translateX(-50%); z-index:1060;
        background:var(--sidebar); color:#fff; padding:.6rem 1rem;
        border-radius:100px; font-size:.78rem; font-weight:500;
        box-shadow:var(--shadow); animation:toastIn .35s ease both;
        display:flex; align-items:center; gap:.5rem;
    }
    @keyframes toastIn{ from{opacity:0;transform:translateX(-50%) translateY(10px);} to{opacity:1;transform:translateX(-50%) translateY(0);} }

    /* ================= HAPTIC-LIKE press feedback ================= */
    .press-scale{ transition:transform .08s; }
    .press-scale:active{ transform:scale(.95); }

    @media (prefers-reduced-motion:reduce){ *,*::before,*::after{ animation-duration:.001ms!important; transition-duration:.001ms!important; } }
    </style>
    @stack('head')
    <script>
    /* Early detect: Android desktop mode atau layar besar → force-desktop class di body */
    (function(){
        var ua = navigator.userAgent;
        var isAndroidDesktop = /Android/.test(ua) && !/Mobile/.test(ua);
        var isDesktop = window.innerWidth >= 992 || isAndroidDesktop;
        if(isDesktop){
            document.documentElement.classList.add('is-desktop');
        }
    })();
    </script>
</head>
<body>
    @php
        $u = auth()->user();
        $nr = $navRouter ?? null;
        $r = request();
    @endphp

    {{-- ========== DESKTOP SIDEBAR (hidden on mobile) ========== --}}
    <nav id="sidebar">
        <div class="brand">
            <span class="mark"><i class="bi bi-broadcast-pin"></i></span>
            <span class="wm">Hotspot<small>Kontrol Sekolah</small></span>
        </div>
        <ul class="nav flex-column">
            <li><a class="nav-link {{ $r->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}" data-tip="Dashboard"><i class="bi bi-grid-1x2"></i><span class="lk-text">Dashboard</span></a></li>
            <li><a class="nav-link {{ $r->routeIs('hotspot.monitor') ? 'active' : '' }}" href="{{ route('hotspot.monitor') }}" data-tip="Monitoring Sesi"><i class="bi bi-broadcast"></i><span class="lk-text">Monitoring Sesi</span></a></li>
            <li><a class="nav-link {{ $r->routeIs('bandwidth.*') ? 'active' : '' }}" href="{{ route('bandwidth.index') }}" data-tip="Bandwidth"><i class="bi bi-speedometer2"></i><span class="lk-text">Bandwidth</span></a></li>

            <div class="nav-heading">Manajemen</div>
            <li><a class="nav-link {{ $r->routeIs('students.*') ? 'active' : '' }}" href="{{ route('students.index') }}" data-tip="Data Siswa"><i class="bi bi-mortarboard"></i><span class="lk-text">Data Siswa</span></a></li>
            <li><a class="nav-link {{ $r->routeIs('teachers.*') ? 'active' : '' }}" href="{{ route('teachers.index') }}" data-tip="Data Guru &amp; Tendik"><i class="bi bi-person-badge"></i><span class="lk-text">Data Guru &amp; Tendik</span></a></li>
            <li><a class="nav-link {{ $r->routeIs('hotspot.index') ? 'active' : '' }}" href="{{ route('hotspot.index') }}" data-tip="User Hotspot"><i class="bi bi-person-vcard"></i><span class="lk-text">User Hotspot</span></a></li>
            <li><a class="nav-link {{ $r->routeIs('vouchers.*') ? 'active' : '' }}" href="{{ route('vouchers.index') }}" data-tip="Voucher"><i class="bi bi-ticket-perforated"></i><span class="lk-text">Voucher</span></a></li>
            <li><a class="nav-link {{ $r->routeIs('packages.*') ? 'active' : '' }}" href="{{ route('packages.index') }}" data-tip="Paket / Profil"><i class="bi bi-box-seam"></i><span class="lk-text">Paket / Profil</span></a></li>

            <div class="nav-heading">Laporan</div>
            <li><a class="nav-link {{ $r->routeIs('reports.index') ? 'active' : '' }}" href="{{ route('reports.index') }}" data-tip="Laporan"><i class="bi bi-bar-chart"></i><span class="lk-text">Laporan</span></a></li>
            <li><a class="nav-link {{ $r->routeIs('reports.logs') ? 'active' : '' }}" href="{{ route('reports.logs') }}" data-tip="Log Aktivitas"><i class="bi bi-clock-history"></i><span class="lk-text">Log Aktivitas</span></a></li>

            @if($u && $u->isSuperadmin())
            <div class="nav-heading">Sistem</div>
            <li><a class="nav-link {{ $r->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.mikrotik.edit') }}" data-tip="Pengaturan Router"><i class="bi bi-router"></i><span class="lk-text">Pengaturan Router</span></a></li>
            <li><a class="nav-link {{ $r->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}" data-tip="Kelola Admin"><i class="bi bi-shield-lock"></i><span class="lk-text">Kelola Admin</span></a></li>
            @endif
        </ul>

        <div class="sb-toggle" onclick="toggleSidebar()" title="Perkecil sidebar">
            <i class="bi bi-chevron-bar-left"></i><span class="tog-text">Perkecil</span>
        </div>

        <a href="{{ route('profile.edit') }}" class="sb-user">
            <span class="sb-avatar">{{ strtoupper(mb_substr($u?->name ?? 'A',0,1)) }}{{ strtoupper(mb_substr(strstr($u?->name.' ',' '),1,1)) }}</span>
            <span class="sb-uinfo">
                <span class="sb-uname d-block">{{ $u?->name }}</span>
                <span class="sb-urole">{{ ucfirst($u?->role) }}</span>
            </span>
        </a>
    </nav>

    <div class="scrim" id="scrim" onclick="toggleNav(false)"></div>

    {{-- ========== MAIN CONTENT ========== --}}
    <div id="content">
        <header class="topbar">
            <button class="burger burger-mobile" onclick="toggleNav()"><i class="bi bi-list"></i></button>
            <button class="burger burger-desktop" onclick="toggleSidebar()" title="Toggle sidebar"><i class="bi bi-layout-sidebar-inset"></i></button>
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

    {{-- ========== BOTTOM NAVIGATION (mobile only) ========== --}}
    <nav id="bottomNav">
        <div class="bnav-inner">
            <a href="{{ route('dashboard') }}" class="bnav-item {{ $r->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2{{ $r->routeIs('dashboard') ? '-fill' : '' }}"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('hotspot.monitor') }}" class="bnav-item {{ $r->routeIs('hotspot.monitor') ? 'active' : '' }}">
                <i class="bi bi-broadcast{{ $r->routeIs('hotspot.monitor') ? '' : '' }}"></i>
                <span>Monitor</span>
            </a>
            <a href="{{ route('bandwidth.index') }}" class="bnav-item {{ $r->routeIs('bandwidth.*') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span>Bandwidth</span>
            </a>
            <a href="{{ route('vouchers.index') }}" class="bnav-item {{ $r->routeIs('vouchers.*') ? 'active' : '' }}">
                <i class="bi bi-ticket-perforated{{ $r->routeIs('vouchers.*') ? '-fill' : '' }}"></i>
                <span>Voucher</span>
            </a>
            <a href="javascript:void(0)" class="bnav-item" id="moreBtn" onclick="toggleMore()">
                <i class="bi bi-grid-3x3-gap-fill"></i>
                <span>Lainnya</span>
            </a>
        </div>
    </nav>

    {{-- ========== "MORE" BOTTOM SHEET ========== --}}
    <div class="sheet-scrim" id="sheetScrim" onclick="toggleMore(false)"></div>
    <div id="moreSheet">
        <div class="sheet-handle"></div>

        <div class="sheet-section">Manajemen</div>
        <div class="sheet-grid">
            <a href="{{ route('students.index') }}" class="sheet-item {{ $r->routeIs('students.*') ? 'active' : '' }}">
                <i class="bi bi-mortarboard-fill"></i>Data Siswa
            </a>
            <a href="{{ route('teachers.index') }}" class="sheet-item {{ $r->routeIs('teachers.*') ? 'active' : '' }}">
                <i class="bi bi-person-badge-fill"></i>Data Guru
            </a>
            <a href="{{ route('hotspot.index') }}" class="sheet-item {{ $r->routeIs('hotspot.index') ? 'active' : '' }}">
                <i class="bi bi-person-vcard-fill"></i>User Hotspot
            </a>
            <a href="{{ route('packages.index') }}" class="sheet-item {{ $r->routeIs('packages.*') ? 'active' : '' }}">
                <i class="bi bi-box-seam-fill"></i>Paket
            </a>
        </div>

        <div class="sheet-section">Laporan & Sistem</div>
        <div class="sheet-grid">
            <a href="{{ route('reports.index') }}" class="sheet-item {{ $r->routeIs('reports.index') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-fill"></i>Laporan
            </a>
            <a href="{{ route('reports.logs') }}" class="sheet-item {{ $r->routeIs('reports.logs') ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i>Log Aktivitas
            </a>
            @if($u && $u->isSuperadmin())
            <a href="{{ route('settings.mikrotik.edit') }}" class="sheet-item {{ $r->routeIs('settings.*') ? 'active' : '' }}">
                <i class="bi bi-router-fill"></i>Router
            </a>
            <a href="{{ route('users.index') }}" class="sheet-item {{ $r->routeIs('users.*') ? 'active' : '' }}">
                <i class="bi bi-shield-lock-fill"></i>Admin
            </a>
            @endif
            <a href="{{ route('profile.edit') }}" class="sheet-item {{ $r->routeIs('profile.*') ? 'active' : '' }}">
                <i class="bi bi-person-gear"></i>Profil
            </a>
            <a href="javascript:void(0)" class="sheet-item" onclick="event.preventDefault();document.getElementById('logoutForm').submit();">
                <i class="bi bi-box-arrow-right" style="color:var(--danger)"></i><span style="color:var(--danger)">Keluar</span>
            </a>
        </div>
        <form id="logoutForm" method="POST" action="{{ route('logout') }}" class="d-none">@csrf</form>
    </div>

    {{-- ========== PULL-TO-REFRESH indicator ========== --}}
    <div id="pullIndicator"><div class="spinner-border" role="status"></div></div>

    @stack('modals')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    /* Desktop sidebar toggle (expand/collapse) */
    function toggleSidebar(){
        const sb = document.getElementById('sidebar');
        const mini = sb.classList.toggle('mini');
        document.body.classList.toggle('sb-mini', mini);
        localStorage.setItem('sb-mini', mini ? '1' : '0');
    }

    /* Restore sidebar mini state from localStorage */
    (function(){
        if(document.documentElement.classList.contains('is-desktop') && localStorage.getItem('sb-mini') === '1'){
            document.getElementById('sidebar').classList.add('mini');
            document.body.classList.add('sb-mini');
        }
    })();

    /* Desktop sidebar toggle (mobile overlay) */
    function toggleNav(force){
        const sb=document.getElementById('sidebar'), sc=document.getElementById('scrim');
        const show = force===undefined ? !sb.classList.contains('show') : force;
        sb.classList.toggle('show', show); sc.classList.toggle('show', show);
    }

    /* Bottom sheet "More" */
    function toggleMore(force){
        const sh=document.getElementById('moreSheet'), sc=document.getElementById('sheetScrim');
        const open = force===undefined ? !sh.classList.contains('open') : force;
        sh.classList.toggle('open', open);
        sc.classList.toggle('open', open);
        /* prevent body scroll when sheet open */
        document.body.style.overflow = open ? 'hidden' : '';
    }

    /* Swipe down to close sheet */
    (function(){
        const sh=document.getElementById('moreSheet');
        let startY=0, currentY=0, dragging=false;
        sh.addEventListener('touchstart', e => {
            if(sh.scrollTop > 5) return;
            startY = e.touches[0].clientY; dragging = true;
        }, {passive:true});
        sh.addEventListener('touchmove', e => {
            if(!dragging) return;
            currentY = e.touches[0].clientY;
            const dy = currentY - startY;
            if(dy > 0) sh.style.transform = `translateY(${dy}px)`;
        }, {passive:true});
        sh.addEventListener('touchend', () => {
            if(!dragging) return; dragging = false;
            const dy = currentY - startY;
            sh.style.transform = '';
            if(dy > 80) toggleMore(false);
            currentY = 0;
        });
    })();

    /* Pull-to-refresh (mobile) — threshold tinggi supaya tidak false-trigger */
    (function(){
        const ind = document.getElementById('pullIndicator');
        let startY=0, pulling=false, confirmed=false;
        document.addEventListener('touchstart', e => {
            if(window.scrollY === 0 && !document.documentElement.classList.contains('is-desktop')){
                /* Jangan trigger kalau touch dimulai di elemen scrollable (tabel, modal, sheet) */
                var t = e.target;
                while(t && t !== document.body){
                    if(t.scrollHeight > t.clientHeight + 5 && t.scrollTop > 0) return;
                    if(t.id === 'moreSheet' || t.classList.contains('modal')) return;
                    t = t.parentElement;
                }
                startY = e.touches[0].clientY; pulling = true; confirmed = false;
            }
        }, {passive:true});
        document.addEventListener('touchmove', e => {
            if(!pulling) return;
            const dy = e.touches[0].clientY - startY;
            /* Kalau user scroll ke atas (dy negatif), batalkan */
            if(dy < 0){ pulling = false; ind.classList.remove('pulling'); return; }
            /* Threshold 120px supaya tidak sensitif */
            if(dy > 120){ ind.classList.add('pulling'); confirmed = true; }
            else{ ind.classList.remove('pulling'); confirmed = false; }
        }, {passive:true});
        document.addEventListener('touchend', () => {
            if(!pulling){ return; }
            pulling = false;
            if(confirmed){
                location.reload();
            }
            ind.classList.remove('pulling');
        });
    })();

    /* Haptic feedback on bnav tap (if supported) */
    document.querySelectorAll('.bnav-item').forEach(el => {
        el.addEventListener('click', () => {
            if(navigator.vibrate) navigator.vibrate(8);
        });
    });
    /* AJAX table loader — dipakai untuk search, sort, dan pagination tanpa full reload */
    (function(){
        var timer, lastVal='', abortCtrl;

        /* Core: fetch URL, replace #ajax-table content */
        function ajaxLoad(url, pushState){
            if(abortCtrl) abortCtrl.abort();
            abortCtrl = new AbortController();
            if(pushState !== false) history.replaceState(null, '', url);

            fetch(url, {
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                signal: abortCtrl.signal
            })
            .then(function(r){ return r.text(); })
            .then(function(html){
                var target = document.getElementById('ajax-table');
                if(target){
                    target.innerHTML = html;
                    var sa = document.getElementById('selectAll');
                    if(sa) sa.checked = false;
                }
                hideSpinner();
            })
            .catch(function(e){
                if(e.name !== 'AbortError') hideSpinner();
            });
        }

        function hideSpinner(){
            document.querySelectorAll('.live-search-spinner').forEach(function(s){ s.classList.add('d-none'); });
        }

        /* Live search with debounce */
        document.querySelectorAll('.live-search').forEach(function(input){
            var form = input.closest('form');

            form.addEventListener('submit', function(e){
                e.preventDefault();
                doSearch(input.value.trim());
            });

            input.addEventListener('input', function(){
                var val = this.value.trim();
                var spinner = form.querySelector('.live-search-spinner');
                clearTimeout(timer);
                if(val === lastVal){ if(spinner) spinner.classList.add('d-none'); return; }
                if(val.length >= 2 || val.length === 0){
                    if(spinner) spinner.classList.remove('d-none');
                    timer = setTimeout(function(){ doSearch(val); }, 400);
                } else {
                    if(spinner) spinner.classList.add('d-none');
                }
            });

            input.addEventListener('search', function(){
                if(this.value === '' && lastVal !== '') doSearch('');
            });

            function doSearch(val){
                lastVal = val;
                var url = new URL(window.location);
                if(val) url.searchParams.set('q', val);
                else url.searchParams.delete('q');
                url.searchParams.delete('page');
                ajaxLoad(url);
            }
        });

        /* Intercept clicks inside #ajax-table: sort links, pagination links */
        document.addEventListener('click', function(e){
            var target = document.getElementById('ajax-table');
            if(!target) return;

            /* Find closest <a> inside #ajax-table */
            var link = e.target.closest('#ajax-table a[href]');
            if(!link) return;
            var href = link.getAttribute('href');
            if(!href || href === '#' || href.startsWith('javascript:')) return;

            /* Only intercept GET navigation links (sort headers, pagination) */
            if(link.closest('form')) return; /* skip links inside forms */

            e.preventDefault();
            ajaxLoad(href);
        });
    })();
    </script>
    @stack('scripts')
</body>
</html>
