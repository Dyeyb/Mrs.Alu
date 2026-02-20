<?php
// ─────────────────────────────────────────────
//  layout.php – Shared Page Layout
//  Include at the top of every page.
//  Expects these variables to be set before including:
//    $pageTitle   (string) – shown in <title> and topbar
//    $activePage  (string) – 'dashboard' | 'users' | 'archive'
// ─────────────────────────────────────────────

$pageTitle  = $pageTitle  ?? 'Dashboard';
$activePage = $activePage ?? 'dashboard';

require_once __DIR__ . '/helpers.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> – Mrs. Alu Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600;700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* ── RESET & CSS VARS ── */
        *{margin:0;padding:0;box-sizing:border-box;}
        :root{
            --gold:        #c9a561;
            --gold-light:  #e8c97a;
            --dark:        #1a1a1a;
            --text:        #2d2d2d;
            --border:      #e5e0d8;
            --bg-page:     #f4f1eb;
            --bg-hover:    #f8f5f0;
            --shadow:      0 4px 20px rgba(0,0,0,.08);
            --ease:        all .35s cubic-bezier(.4,0,.2,1);
            --sidebar-w:   270px;
            --topbar-h:    70px;
        }
        body{font-family:'Outfit',sans-serif;background:var(--bg-page);min-height:100vh;color:var(--text);display:flex;}

        /* ── SIDEBAR ── */
        .sidebar{width:var(--sidebar-w);min-height:100vh;position:fixed;top:0;left:0;display:flex;flex-direction:column;overflow:hidden;z-index:200;}
        .sb-bg{position:absolute;inset:0;z-index:0;}
        .sb-buildings{position:absolute;bottom:0;left:0;width:100%;height:65%;opacity:.18;z-index:1;}
        .sb-gradient{position:absolute;inset:0;background:linear-gradient(160deg,#1a0e05 0%,#2d1a08 20%,#3d2410 40%,#4a2e18 60%,#5c3820 75%,#3a2010 88%,#1a0e05 100%);z-index:2;}
        .sb-pattern{position:absolute;inset:0;z-index:3;background-image:url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none'%3E%3Cg fill='%23c9a561' fill-opacity='0.05'%3E%3Cpath d='M20 20v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-16V0h-2v4H14v2h4v4h2V6h4V4h-4zM4 20v-4H2v4H-2v2h4v4h2v-4h4v-2H4z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");opacity:.6;}
        .sb-inner{position:relative;z-index:10;display:flex;flex-direction:column;height:100%;}

        /* Logo */
        .sb-logo{display:flex;align-items:center;gap:12px;padding:24px 24px 20px;border-bottom:1px solid rgba(201,165,97,.2);text-decoration:none;}
        .sb-logo-icon{width:44px;height:44px;background:linear-gradient(135deg,#c9a561,#e8c97a);border-radius:8px;display:flex;align-items:center;justify-content:center;font-family:'Cormorant Garamond',serif;font-size:26px;font-weight:700;color:#1a0e05;flex-shrink:0;box-shadow:0 4px 12px rgba(201,165,97,.3);}
        .sb-logo-name{font-family:'Cormorant Garamond',serif;font-size:22px;font-weight:600;color:#fff;line-height:1;}
        .sb-logo-sub{font-size:11px;color:rgba(201,165,97,.7);letter-spacing:1.5px;text-transform:uppercase;margin-top:3px;}

        /* Nav */
        .sb-nav{flex:1;padding:8px 0;overflow-y:auto;}
        .sb-nav::-webkit-scrollbar{width:3px;}
        .sb-nav::-webkit-scrollbar-thumb{background:rgba(201,165,97,.3);border-radius:10px;}
        .nav-section{font-size:10px;font-weight:600;letter-spacing:3px;text-transform:uppercase;color:rgba(201,165,97,.5);padding:20px 24px 8px;}
        .nav-link{display:flex;align-items:center;gap:12px;padding:11px 24px;text-decoration:none;color:rgba(255,255,255,.65);font-size:14px;font-weight:500;transition:var(--ease);position:relative;}
        .nav-link::before{content:'';position:absolute;left:0;top:50%;transform:translateY(-50%) scaleY(0);width:3px;height:60%;background:var(--gold);border-radius:0 2px 2px 0;transition:transform .25s ease;}
        .nav-link:hover,.nav-link.active{color:#fff;background:rgba(201,165,97,.1);}
        .nav-link:hover::before,.nav-link.active::before{transform:translateY(-50%) scaleY(1);}
        .nav-link.active{background:rgba(201,165,97,.15);}
        .nav-link svg{width:18px;height:18px;flex-shrink:0;opacity:.8;}
        .nav-link:hover svg,.nav-link.active svg{opacity:1;}

        /* Sidebar user footer */
        .sb-footer{padding:16px 24px;border-top:1px solid rgba(201,165,97,.15);}
        .sb-user{display:flex;align-items:center;gap:10px;}
        .sb-avatar{width:36px;height:36px;background:linear-gradient(135deg,#c9a561,#e8c97a);border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:'Cormorant Garamond',serif;font-size:16px;font-weight:700;color:#1a0e05;flex-shrink:0;}
        .sb-uname{font-size:13px;font-weight:600;color:#fff;}
        .sb-urole{font-size:11px;color:rgba(201,165,97,.7);}

        /* ── MAIN AREA ── */
        .main-wrap{margin-left:var(--sidebar-w);flex:1;min-height:100vh;display:flex;flex-direction:column;}

        /* ── TOPBAR ── */
        .topbar{height:var(--topbar-h);background:#fff;border-bottom:1px solid var(--border);box-shadow:var(--shadow);display:flex;align-items:center;justify-content:space-between;padding:0 36px;position:sticky;top:0;z-index:100;}
        .topbar-title{font-family:'Cormorant Garamond',serif;font-size:26px;font-weight:600;color:var(--dark);}
        .topbar-title span{color:var(--gold);font-style:italic;}
        .topbar-crumb{display:flex;align-items:center;gap:6px;font-size:13px;color:#aaa;margin-top:2px;}
        .topbar-crumb a{color:#aaa;text-decoration:none;}
        .topbar-crumb a:hover{color:var(--gold);}
        .topbar-crumb .sep{color:#ddd;}
        .topbar-crumb .cur{color:var(--gold);font-weight:500;}
        .topbar-right{display:flex;align-items:center;gap:14px;}
        .tb-search{position:relative;display:flex;align-items:center;}
        .tb-search input{width:200px;padding:8px 36px 8px 14px;border:1px solid var(--border);border-radius:50px;font-family:'Outfit',sans-serif;font-size:13px;background:var(--bg-hover);outline:none;transition:var(--ease);color:var(--text);}
        .tb-search input:focus{border-color:var(--gold);width:240px;}
        .tb-search input::placeholder{color:#bbb;}
        .tb-search-btn{position:absolute;right:4px;width:28px;height:28px;background:var(--gold);border:none;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;}
        .tb-search-btn svg{width:13px;height:13px;color:#fff;}
        .icon-btn{width:38px;height:38px;border-radius:50%;border:1px solid var(--border);background:var(--bg-hover);display:flex;align-items:center;justify-content:center;cursor:pointer;transition:var(--ease);position:relative;color:#888;}
        .icon-btn:hover{border-color:var(--gold);color:var(--gold);background:#fff;}
        .icon-btn svg{width:16px;height:16px;}
        .notif-dot{position:absolute;top:6px;right:7px;width:7px;height:7px;background:#e74c3c;border-radius:50%;border:1px solid #fff;}
        .tb-avatar{width:38px;height:38px;background:linear-gradient(135deg,#c9a561,#e8c97a);border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:'Cormorant Garamond',serif;font-size:18px;font-weight:700;color:#1a0e05;cursor:pointer;border:2px solid transparent;transition:var(--ease);}
        .tb-avatar:hover{border-color:var(--gold);}

        /* ── FLASH ── */
        .flash{margin:24px 36px 0;padding:14px 20px;border-radius:10px;font-size:14px;display:flex;align-items:center;gap:10px;animation:fadeUp .4s ease-out both;}
        .flash-success{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;}
        .flash-error  {background:#fef2f2;border:1px solid #fecaca;color:#dc2626;}
        .flash-warning{background:#fffbeb;border:1px solid #fde68a;color:#92400e;}
        .flash svg{width:18px;height:18px;flex-shrink:0;}

        /* ── GLOBAL BUTTONS ── */
        .btn{display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:8px;font-family:'Outfit',sans-serif;font-size:13px;font-weight:600;cursor:pointer;transition:var(--ease);border:none;text-decoration:none;letter-spacing:.2px;}
        .btn svg{width:15px;height:15px;flex-shrink:0;}
        .btn-sm{padding:6px 12px;font-size:12px;}
        .btn-sm svg{width:13px;height:13px;}
        .btn-primary{background:linear-gradient(135deg,#c9a561,#e8c97a);color:#1a0e05;box-shadow:0 4px 12px rgba(201,165,97,.3);}
        .btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(201,165,97,.4);}
        .btn-secondary{background:#f0ebe0;color:var(--text);border:1px solid var(--border);}
        .btn-secondary:hover{background:#e5ddd0;border-color:var(--gold);color:var(--gold);}
        .btn-edit{background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;}
        .btn-edit:hover{background:#dbeafe;border-color:#2563eb;}
        .btn-archive{background:#fffbeb;color:#92400e;border:1px solid #fde68a;}
        .btn-archive:hover{background:#fef3c7;border-color:#92400e;}
        .btn-restore{background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;}
        .btn-restore:hover{background:#dcfce7;border-color:#166534;}
        .btn-danger{background:#fef2f2;color:#dc2626;border:1px solid #fecaca;}
        .btn-danger:hover{background:#fee2e2;border-color:#dc2626;}

        /* ── TAG BADGES ── */
        .tag{display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;letter-spacing:.5px;}
        .tag-admin{background:#fef3c7;color:#92400e;border:1px solid #fde68a;}
        .tag-user{background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;}

        /* ── MODAL ── */
        .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(4px);z-index:1000;align-items:center;justify-content:center;}
        .modal-overlay.open{display:flex;}
        .modal{background:#fff;border-radius:16px;width:90%;max-width:520px;box-shadow:0 24px 60px rgba(0,0,0,.2);animation:fadeUp .3s ease-out both;overflow:hidden;}
        .modal-lg{max-width:560px;}
        .modal-sm{max-width:420px;}
        .modal-header{display:flex;align-items:center;justify-content:space-between;padding:22px 28px;border-bottom:1px solid var(--border);background:#fefcf8;}
        .modal-title{font-family:'Cormorant Garamond',serif;font-size:22px;font-weight:600;color:var(--dark);}
        .modal-title span{color:var(--gold);font-style:italic;}
        .modal-close{width:32px;height:32px;border-radius:50%;border:1px solid var(--border);background:var(--bg-hover);display:flex;align-items:center;justify-content:center;cursor:pointer;color:#888;font-size:20px;line-height:1;transition:var(--ease);}
        .modal-close:hover{border-color:#dc2626;color:#dc2626;background:#fef2f2;}
        .modal-body{padding:28px;}
        .modal-footer{display:flex;align-items:center;justify-content:flex-end;gap:10px;padding:18px 28px;border-top:1px solid var(--border);background:#fafaf8;}

        /* ── FORM ── */
        .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;}
        .form-group{display:flex;flex-direction:column;gap:6px;}
        .form-group.full{grid-column:1/-1;}
        .form-label{font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#888;}
        .form-input,.form-select{padding:10px 14px;border:1px solid var(--border);border-radius:8px;font-family:'Outfit',sans-serif;font-size:14px;background:#fafaf8;color:var(--text);outline:none;transition:var(--ease);}
        .form-input:focus,.form-select:focus{border-color:var(--gold);background:#fff;box-shadow:0 0 0 3px rgba(201,165,97,.1);}
        .form-input::placeholder{color:#ccc;}
        .form-hint{font-size:11px;color:#bbb;}

        /* ── CONFIRM MODAL BODY ── */
        .confirm-body{padding:32px 28px;text-align:center;}
        .confirm-icon{width:68px;height:68px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 18px;}
        .confirm-icon svg{width:30px;height:30px;}
        .ci-archive{background:#fffbeb;} .ci-archive svg{color:#92400e;}
        .ci-delete{background:#fef2f2;}  .ci-delete  svg{color:#dc2626;}
        .ci-restore{background:#f0fdf4;} .ci-restore svg{color:#166534;}
        .confirm-title{font-family:'Cormorant Garamond',serif;font-size:22px;font-weight:600;color:var(--dark);margin-bottom:8px;}
        .confirm-desc{font-size:14px;color:#888;line-height:1.7;}
        .confirm-name{font-weight:700;color:var(--dark);}

        /* ── ANIMATIONS ── */
        @keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
        .anim  {animation:fadeUp .5s ease-out both;}
        .d1{animation-delay:.05s;}.d2{animation-delay:.10s;}.d3{animation-delay:.15s;}.d4{animation-delay:.20s;}.d5{animation-delay:.25s;}

        /* ── PAGE CONTENT ── */
        .page{flex:1;padding:36px;}
    </style>
</head>
<body>

<!-- ══════════════════ SIDEBAR ══════════════════ -->
<aside class="sidebar">
    <div class="sb-bg">
        <svg class="sb-buildings" viewBox="0 0 270 500" fill="white" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <!-- Background skyline -->
            <rect x="0"   y="160" width="22" height="340"/>
            <rect x="25"  y="80"  width="18" height="420"/>
            <rect x="46"  y="200" width="28" height="300"/>
            <rect x="78"  y="60"  width="20" height="440"/>
            <rect x="102" y="140" width="24" height="360"/>
            <rect x="130" y="40"  width="16" height="460"/>
            <rect x="150" y="180" width="30" height="320"/>
            <rect x="184" y="100" width="22" height="400"/>
            <rect x="210" y="220" width="18" height="280"/>
            <rect x="232" y="70"  width="20" height="430"/>
            <rect x="256" y="190" width="14" height="310"/>
            <!-- Mid-ground -->
            <rect x="10"  y="260" width="35" height="240"/>
            <rect x="50"  y="200" width="28" height="300"/>
            <rect x="85"  y="290" width="22" height="210"/>
            <rect x="112" y="240" width="40" height="260"/>
            <rect x="158" y="270" width="30" height="230"/>
            <rect x="193" y="220" width="36" height="280"/>
            <rect x="234" y="260" width="28" height="240"/>
            <!-- Foreground -->
            <rect x="0"   y="340" width="55" height="160"/>
            <rect x="60"  y="310" width="45" height="190"/>
            <rect x="110" y="360" width="60" height="140"/>
            <rect x="175" y="330" width="50" height="170"/>
            <rect x="230" y="350" width="40" height="150"/>
            <!-- Gold windows -->
            <rect x="5"   y="350" width="6" height="6" fill="#c9a561" opacity="0.5"/>
            <rect x="15"  y="350" width="6" height="6" fill="#c9a561" opacity="0.3"/>
            <rect x="5"   y="362" width="6" height="6" fill="#c9a561" opacity="0.2"/>
            <rect x="15"  y="362" width="6" height="6" fill="#c9a561" opacity="0.5"/>
            <rect x="30"  y="350" width="6" height="6" fill="#c9a561" opacity="0.3"/>
            <rect x="68"  y="320" width="6" height="6" fill="#c9a561" opacity="0.4"/>
            <rect x="80"  y="320" width="6" height="6" fill="#c9a561" opacity="0.2"/>
            <rect x="68"  y="332" width="6" height="6" fill="#c9a561" opacity="0.3"/>
            <rect x="118" y="370" width="6" height="6" fill="#c9a561" opacity="0.3"/>
            <rect x="130" y="370" width="6" height="6" fill="#c9a561" opacity="0.5"/>
            <rect x="155" y="370" width="6" height="6" fill="#c9a561" opacity="0.4"/>
            <rect x="183" y="340" width="6" height="6" fill="#c9a561" opacity="0.4"/>
            <rect x="210" y="340" width="6" height="6" fill="#c9a561" opacity="0.5"/>
            <!-- Antenna spires -->
            <rect x="128" y="20"  width="4" height="40"/>
            <rect x="77"  y="50"  width="3" height="30"/>
            <rect x="23"  y="68"  width="3" height="25"/>
            <rect x="183" y="92"  width="3" height="30"/>
        </svg>
        <div class="sb-gradient"></div>
        <div class="sb-pattern"></div>
    </div>

    <div class="sb-inner">
        <!-- Logo -->
        <a href="index.php" class="sb-logo">
            <div class="sb-logo-icon">M</div>
            <div>
                <div class="sb-logo-name">Mrs.Alu</div>
                <div class="sb-logo-sub">Admin Panel</div>
            </div>
        </a>

        <!-- Navigation -->
        <nav class="sb-nav">
            <div class="nav-section">Main Menu</div>
            <a href="index.php" class="nav-link <?= $activePage==='dashboard'?'active':'' ?>">
                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="14" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                </svg>
                Dashboard
            </a>

            <div class="nav-section">User Management</div>
            <a href="users.php" class="nav-link <?= $activePage==='users'?'active':'' ?>">
                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                All Users
            </a>
            <a href="add-users.php" class="nav-link <?= $activePage==='add-users'?'active':'' ?>">
                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <line x1="19" y1="8" x2="19" y2="14"/>
                    <line x1="22" y1="11" x2="16" y2="11"/>
                </svg>
                Add User
            </a>
            <a href="archive-users.php" class="nav-link <?= $activePage==='archive-users'?'active':'' ?>">
                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <polyline points="21 8 21 21 3 21 3 8"/>
                    <rect x="1" y="3" width="22" height="5"/>
                    <line x1="10" y1="12" x2="14" y2="12"/>
                </svg>
                Archived Users
            </a>

            <div class="nav-section">Content</div>
            <a href="#" class="nav-link <?= $activePage==='products'?'active':'' ?>">
                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M20 7H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                </svg>
                Products
            </a>
            <a href="#" class="nav-link <?= $activePage==='news'?'active':'' ?>">
                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                News
            </a>
            <a href="#" class="nav-link <?= $activePage==='cases'?'active':'' ?>">
                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <path d="M3 9h18M9 21V9"/>
                </svg>
                Cases
            </a>

            <div class="nav-section">System</div>
            <a href="#" class="nav-link">
                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
                Settings
            </a>
            <a href="#" class="nav-link">
                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16,17 21,12 16,7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                Logout
            </a>
        </nav>

        <!-- User footer -->
        <div class="sb-footer">
            <div class="sb-user">
                <div class="sb-avatar">A</div>
                <div>
                    <div class="sb-uname">Administrator</div>
                    <div class="sb-urole">Super Admin</div>
                </div>
            </div>
        </div>
    </div>
</aside>

<!-- ══════════════════ MAIN ══════════════════ -->
<div class="main-wrap">

    <!-- TOPBAR -->
    <header class="topbar">
        <div>
            <div class="topbar-title"><?php
                $parts = explode(' ', $pageTitle, 2);
                if (count($parts) === 2) echo e($parts[0]).' <span>'.e($parts[1]).'</span>';
                else echo e($pageTitle);
            ?></div>
            <div class="topbar-crumb">
                <a href="index.php">Dashboard</a>
                <?php if (!empty($breadcrumb)): ?>
                    <?php foreach ($breadcrumb as $label => $url): ?>
                        <span class="sep">›</span>
                        <?php if ($url): ?>
                            <a href="<?= e($url) ?>"><?= e($label) ?></a>
                        <?php else: ?>
                            <span class="cur"><?= e($label) ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="topbar-right">
            <div class="tb-search">
                <input type="text" placeholder="Search...">
                <button class="tb-search-btn">
                    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                    </svg>
                </button>
            </div>
            <div class="icon-btn">
                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                <div class="notif-dot"></div>
            </div>
            <div class="tb-avatar">A</div>
        </div>
    </header>

    <?php
    // ── Flash message ──────────────────────────
    $flash = getFlash();
    if ($flash): ?>
    <div class="flash flash-<?= e($flash['type']) ?>" style="margin:24px 36px 0;">
        <?php if ($flash['type']==='success'): ?>
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        <?php elseif ($flash['type']==='error'): ?>
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <?php else: ?>
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        <?php endif; ?>
        <?= e($flash['message']) ?>
    </div>
    <?php endif; ?>

    <!-- ═══════════ PAGE CONTENT START ═══════════ -->
    <div class="page">