<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ ($isRtl ?? true) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1e293b">

    <title>@yield('title', config('app.name')) | {{ config('app.name') }}</title>

    {{-- Bootstrap 5 RTL --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">

    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    {{-- DataTables Bootstrap 5 --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

    {{-- Select2 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.rtl.min.css">

    {{-- SweetAlert2 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    {{-- Toastr --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    {{-- Cairo Font (Arabic) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * { -webkit-tap-highlight-color: transparent; }
        html, body { margin: 0; padding: 0; }
        body { font-family: 'Cairo', sans-serif; background: #f4f6f9; }

        /* ── Sidebar (desktop fixed, mobile offcanvas) ─────────── */
        .sidebar {
            background: #1e293b;
            color: #fff;
            width: 250px;
            min-height: 100vh;
            flex-shrink: 0;
        }
        .sidebar a {
            color: #cbd5e1;
            text-decoration: none;
            padding: 0.65rem 1rem;
            display: block;
            border-radius: 6px;
            margin: 2px 0;
        }
        .sidebar a:hover, .sidebar a.active { background: #334155; color: #fff; }
        .sidebar .brand {
            font-size: 1.25rem;
            font-weight: 700;
            padding: 1rem;
            border-bottom: 1px solid #334155;
        }

        /* On mobile (sm), turn sidebar into offcanvas behavior */
        @media (max-width: 991.98px) {
            .sidebar {
                position: fixed;
                top: 0; bottom: 0; right: 0;
                z-index: 1045;
                transform: translateX(100%);
                transition: transform 0.3s ease;
                overflow-y: auto;
                box-shadow: -2px 0 10px rgba(0,0,0,0.2);
            }
            .sidebar.open { transform: translateX(0); }
            .sidebar-backdrop {
                position: fixed; inset: 0;
                background: rgba(0,0,0,0.5);
                z-index: 1044;
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.3s;
            }
            .sidebar-backdrop.show { opacity: 1; pointer-events: auto; }
        }

        /* ── Topbar ────────────────────────────────────────────── */
        .topbar {
            background: #fff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 1030;
        }
        .topbar .navbar-brand { font-size: 1rem; }

        /* ── Main content ──────────────────────────────────────── */
        .main-content { flex: 1; min-width: 0; }
        main.page { padding: 1rem; }
        @media (min-width: 768px) { main.page { padding: 1.5rem; } }

        /* ── KPI cards ─────────────────────────────────────────── */
        .kpi-card { border: none; border-radius: 10px; transition: transform .2s; }
        .kpi-card:hover { transform: translateY(-3px); }
        .kpi-card .card-body small { font-size: .8rem; }

        /* ── Tables ────────────────────────────────────────────── */
        .table-responsive { background: #fff; border-radius: 8px; }

        /* Make DataTable horizontally scrollable on mobile */
        @media (max-width: 768px) {
            .dataTables_wrapper { overflow-x: auto; }
            table.dataTable { font-size: .85rem; }
            .dt-buttons { margin-bottom: .5rem; }
            .dt-buttons .btn { font-size: .75rem; padding: .25rem .5rem; }
        }

        /* ── Cards ─────────────────────────────────────────────── */
        @media (max-width: 768px) {
            .card-header h5, .card-header h6 { font-size: 1rem; }
            .card-body { padding: 1rem; }
        }

        /* ── Forms (touch-friendly on mobile) ──────────────────── */
        @media (max-width: 768px) {
            .form-control, .form-select { min-height: 42px; font-size: 16px; }  /* prevents iOS zoom */
            .modal-dialog { margin: 0.5rem; }
            .btn { min-height: 38px; }
        }

        /* ── Badges ────────────────────────────────────────────── */
        .badge-status { padding: 0.4em 0.65em; }
    </style>

    @stack('styles')
</head>
<body>

<div class="d-flex flex-column flex-lg-row min-vh-100">

    {{-- Sidebar (acts as offcanvas on <lg) --}}
    @include('layouts.admin._sidebar')
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    {{-- Main Content --}}
    <div class="main-content">
        {{-- Topbar --}}
        @include('layouts.admin._topbar')

        {{-- Page Content --}}
        <main class="page">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

{{-- jQuery --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

{{-- Bootstrap 5 Bundle --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

{{-- DataTables --}}
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

{{-- Select2 --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Toastr --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    toastr.options = {
        "positionClass": "toast-top-left",
        "rtl": true,
        "closeButton": true,
        "progressBar": true,
        "timeOut": "4000"
    };

    // ── Mobile sidebar toggle ─────────────────────────────────
    (function () {
        const sidebar  = document.querySelector('.sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');

        function open()  { sidebar?.classList.add('open');  backdrop?.classList.add('show'); document.body.style.overflow = 'hidden'; }
        function close() { sidebar?.classList.remove('open'); backdrop?.classList.remove('show'); document.body.style.overflow = ''; }

        document.addEventListener('click', e => {
            if (e.target.closest('[data-toggle-sidebar]')) { e.preventDefault(); open(); }
            if (e.target === backdrop) close();
            // close on link click in mobile
            if (e.target.closest('.sidebar a') && window.innerWidth < 992) close();
        });

        // Close on resize > lg
        window.addEventListener('resize', () => { if (window.innerWidth >= 992) close(); });
    })();

    // ── Default DataTables responsive defaults ────────────────
    if (typeof $.fn.dataTable !== 'undefined') {
        $.extend(true, $.fn.dataTable.defaults, {
            responsive: true,
            autoWidth: false,
            language: window.dtArabic
        });
    }

    // DataTables Arabic translation defaults
    window.dtArabic = {
        "sEmptyTable":     "ليست هناك بيانات متاحة في الجدول",
        "sLoadingRecords": "جارٍ التحميل...",
        "sProcessing":     "جارٍ التحميل...",
        "sLengthMenu":     "أظهر _MENU_ مدخلات",
        "sZeroRecords":    "لم يعثر على أية سجلات",
        "sInfo":           "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
        "sInfoEmpty":      "يعرض 0 إلى 0 من أصل 0 سجل",
        "sInfoFiltered":   "(منتقاة من مجموع _MAX_ مُدخل)",
        "sSearch":         "ابحث:",
        "sUrl":            "",
        "oPaginate": {
            "sFirst":    "الأول",
            "sPrevious": "السابق",
            "sNext":     "التالي",
            "sLast":     "الأخير"
        }
    };
</script>

@stack('scripts')

</body>
</html>
