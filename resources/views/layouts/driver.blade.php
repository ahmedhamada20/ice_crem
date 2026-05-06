<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#6366f1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">

    <title>@yield('title', 'تطبيق السائق') | {{ config('app.name') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root { --brand: #6366f1; --brand-2: #8b5cf6; }

        * { -webkit-tap-highlight-color: transparent; }
        html, body { margin: 0; padding: 0; }
        body {
            font-family: 'Cairo', sans-serif;
            background: #e5e7eb;  /* shows the "phone-outside" gray on desktop */
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Phone-like frame on desktop, fullscreen on mobile */
        .app-shell {
            max-width: 480px;
            margin: 0 auto;
            min-height: 100vh;
            background: #f9fafb;
            position: relative;
            box-shadow: 0 0 40px rgba(0,0,0,.15);
            display: flex;
            flex-direction: column;
        }

        @media (min-width: 768px) {
            body { padding: 1rem 0; }
            .app-shell {
                min-height: calc(100vh - 2rem);
                border-radius: 24px;
                overflow: hidden;
            }
        }

        /* ── Header ─────────────────────────────────────── */
        .app-header {
            background: linear-gradient(135deg, var(--brand), var(--brand-2));
            color: white;
            padding: 1rem 1.25rem 1.25rem;
            position: sticky; top: 0; z-index: 100;
            box-shadow: 0 4px 12px rgba(0,0,0,.08);
        }
        .app-header .greeting { font-size: .85rem; opacity: .9; }
        .app-header .driver-name { font-size: 1.15rem; font-weight: 700; }
        .app-header .profile-circle {
            width: 44px; height: 44px; border-radius: 50%;
            background: rgba(255,255,255,.25); display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem;
        }
        .app-header .icon-btn {
            background: rgba(255,255,255,.18); border: 0; color: white;
            width: 38px; height: 38px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
        }

        /* ── Stats row ──────────────────────────────────── */
        .stats-row {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: .5rem;
            padding: 1rem 1rem 0;
        }
        .stat-card {
            background: white; border-radius: 14px; padding: .8rem .5rem;
            text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,.04);
        }
        .stat-card .num { font-size: 1.4rem; font-weight: 800; color: #1f2937; }
        .stat-card .lbl { font-size: .72rem; color: #6b7280; }
        .stat-card.success .num { color: #059669; }
        .stat-card.warn .num { color: #d97706; }
        .stat-card.danger .num { color: #dc2626; }

        /* ── Main content area ──────────────────────────── */
        .app-main {
            flex: 1;
            padding: 1rem;
            padding-bottom: 5.5rem;  /* space for bottom nav */
        }

        /* ── Delivery cards ─────────────────────────────── */
        .delivery-card {
            background: white;
            border-radius: 16px;
            padding: 1rem;
            margin-bottom: .75rem;
            box-shadow: 0 2px 10px rgba(0,0,0,.05);
            border-right: 4px solid #e5e7eb;
            transition: transform .15s;
        }
        .delivery-card:active { transform: scale(.98); }
        .delivery-card.assigned   { border-right-color: #f59e0b; }
        .delivery-card.in-progress{ border-right-color: var(--brand); }
        .delivery-card.delivered  { border-right-color: #10b981; opacity: .7; }
        .delivery-card .order-num { font-size: .78rem; color: #9ca3af; }
        .delivery-card .customer  { font-weight: 700; font-size: 1.05rem; color: #111827; margin: .15rem 0; }
        .delivery-card .meta      { font-size: .85rem; color: #6b7280; }
        .delivery-card .meta i    { width: 1.1rem; }
        .delivery-card .total     { font-size: 1.15rem; font-weight: 700; color: #059669; }

        .pill {
            display: inline-block; padding: .15rem .65rem; border-radius: 999px;
            font-size: .72rem; font-weight: 600;
        }
        .pill.assigned { background: #fef3c7; color: #92400e; }
        .pill.in-progress { background: #ede9fe; color: #5b21b6; }
        .pill.delivered { background: #d1fae5; color: #065f46; }

        .btn-action {
            background: linear-gradient(135deg, var(--brand), var(--brand-2));
            color: white; border: 0; font-weight: 600;
            padding: .65rem 1rem; border-radius: 10px; width: 100%;
            display: flex; align-items: center; justify-content: center; gap: .5rem;
        }
        .btn-action:hover, .btn-action:focus { color: white; opacity: .92; }
        .btn-action:disabled { opacity: .55; }
        .btn-action.success { background: linear-gradient(135deg, #10b981, #059669); }
        .btn-action.outline { background: white; color: var(--brand); border: 1.5px solid var(--brand); }

        /* ── Bottom navigation ──────────────────────────── */
        .bottom-nav {
            position: sticky; bottom: 0;
            background: white;
            border-top: 1px solid #e5e7eb;
            display: grid; grid-template-columns: repeat(4, 1fr);
            padding: .5rem 0 calc(env(safe-area-inset-bottom) + .5rem);
            z-index: 100;
        }
        .bottom-nav a {
            text-align: center; color: #9ca3af; text-decoration: none;
            font-size: .72rem; padding: .35rem;
            display: flex; flex-direction: column; align-items: center; gap: .15rem;
        }
        .bottom-nav a i { font-size: 1.25rem; }
        .bottom-nav a.active { color: var(--brand); font-weight: 700; }

        /* ── Empty state ───────────────────────────────── */
        .empty {
            text-align: center; padding: 3rem 1rem; color: #9ca3af;
        }
        .empty i { font-size: 3rem; opacity: .4; }

        /* ── SignaturePad canvas ───────────────────────── */
        canvas { background: #fff; border: 1.5px dashed #d1d5db; border-radius: 8px; touch-action: none; }
    </style>

    @stack('styles')
</head>
<body>

<div class="app-shell">

    @hasSection('header')
        @yield('header')
    @else
        <header class="app-header">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="profile-circle">
                        <i class="bi bi-person"></i>
                    </div>
                    <div>
                        <div class="greeting">{{ now()->hour < 12 ? 'صباح الخير،' : (now()->hour < 18 ? 'مساء الخير،' : 'أهلاً،') }}</div>
                        <div class="driver-name">{{ auth()->user()->name }}</div>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    @hasanyrole('super-admin|admin')
                    <a href="{{ route('dashboard') }}" class="icon-btn" title="لوحة التحكم">
                        <i class="bi bi-grid"></i>
                    </a>
                    @endhasanyrole
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button class="icon-btn" type="submit" title="خروج">
                            <i class="bi bi-box-arrow-left"></i>
                        </button>
                    </form>
                </div>
            </div>
        </header>
    @endif

    <main class="app-main">
        @yield('content')
    </main>

    <nav class="bottom-nav">
        <a href="{{ route('deliveries.driver') }}" class="{{ request()->routeIs('deliveries.driver') ? 'active' : '' }}">
            <i class="bi bi-list-check"></i>
            <span>توصيلاتي</span>
        </a>
        <a href="{{ route('deliveries.map') }}" class="{{ request()->routeIs('deliveries.map') ? 'active' : '' }}">
            <i class="bi bi-map"></i>
            <span>الخريطة</span>
        </a>
        <a href="#" id="navHistory">
            <i class="bi bi-clock-history"></i>
            <span>السجل</span>
        </a>
        <a href="{{ route('profile.edit') }}">
            <i class="bi bi-person-circle"></i>
            <span>حسابي</span>
        </a>
    </nav>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
    toastr.options = { positionClass: 'toast-bottom-center', rtl: true, closeButton: true, progressBar: true, timeOut: 3500 };
</script>

@stack('scripts')

</body>
</html>
