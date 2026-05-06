<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ ($isRtl ?? true) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

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
        body { font-family: 'Cairo', sans-serif; background: #f4f6f9; }
        .sidebar { min-height: 100vh; background: #1e293b; color: #fff; }
        .sidebar a { color: #cbd5e1; text-decoration: none; padding: 0.65rem 1rem; display: block; border-radius: 6px; margin: 2px 0; }
        .sidebar a:hover, .sidebar a.active { background: #334155; color: #fff; }
        .sidebar .brand { font-size: 1.25rem; font-weight: 700; padding: 1rem; border-bottom: 1px solid #334155; }
        .topbar { background: #fff; box-shadow: 0 2px 6px rgba(0,0,0,0.05); }
        .kpi-card { border: none; border-radius: 10px; transition: transform .2s; }
        .kpi-card:hover { transform: translateY(-3px); }
        .table-responsive { background: #fff; border-radius: 8px; }
        .badge-status { padding: 0.4em 0.65em; }
    </style>

    @stack('styles')
</head>
<body>

<div class="d-flex">
    {{-- Sidebar --}}
    @include('layouts.admin._sidebar')

    {{-- Main Content --}}
    <div class="flex-grow-1">
        {{-- Topbar --}}
        @include('layouts.admin._topbar')

        {{-- Page Content --}}
        <main class="p-4">
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

{{-- jQuery (required for DataTables, Select2) --}}
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
