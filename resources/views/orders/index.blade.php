@extends('layouts.admin')
@section('title', __('Orders'))
@section('page_title', __('Orders'))

@section('content')

{{-- ═══════════════════════════════════════════════════════════
     KPI Stats Cards
     ═══════════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-stat kpi-primary">
            <div class="kpi-icon"><i class="bi bi-cart-plus"></i></div>
            <div class="kpi-info">
                <small>طلبات اليوم</small>
                <h3>{{ $stats['today_count'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-stat kpi-success">
            <div class="kpi-icon"><i class="bi bi-cash-coin"></i></div>
            <div class="kpi-info">
                <small>إيرادات اليوم</small>
                <h3>{{ number_format($stats['today_revenue'], 0) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-stat kpi-warning">
            <div class="kpi-icon"><i class="bi bi-clock-history"></i></div>
            <div class="kpi-info">
                <small>طلبات معلقة</small>
                <h3>{{ $stats['pending'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-stat kpi-info">
            <div class="kpi-icon"><i class="bi bi-truck"></i></div>
            <div class="kpi-info">
                <small>تم التسليم (الشهر)</small>
                <h3>{{ $stats['delivered_month'] }}</h3>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     Main Card
     ═══════════════════════════════════════════════════════════ --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
        <div>
            <h5 class="mb-0 fw-bold"><i class="bi bi-cart text-primary"></i> {{ __('Orders') }}</h5>
            <small class="text-muted">قائمة جميع الطلبات مع إمكانية التصفية والتصدير</small>
        </div>
        @can('create', App\Models\Order::class)
        <a href="{{ route('orders.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> طلب جديد
        </a>
        @endcan
    </div>

    <div class="card-body">

        {{-- Status Filter Pills --}}
        <div class="d-flex flex-wrap gap-2 mb-3 status-pills">
            <button type="button" class="status-pill active" data-status="">
                <i class="bi bi-grid-fill"></i> الكل
            </button>
            <button type="button" class="status-pill pill-warning" data-status="pending">
                <i class="bi bi-clock"></i> معلق
                @if($stats['pending'] > 0)<span class="count-badge">{{ $stats['pending'] }}</span>@endif
            </button>
            <button type="button" class="status-pill pill-info" data-status="confirmed">
                <i class="bi bi-check-circle"></i> مؤكد
            </button>
            <button type="button" class="status-pill pill-primary" data-status="delivering">
                <i class="bi bi-truck"></i> جاري التوصيل
            </button>
            <button type="button" class="status-pill pill-success" data-status="delivered">
                <i class="bi bi-check2-all"></i> تم التسليم
            </button>
            <button type="button" class="status-pill pill-dark" data-status="returned">
                <i class="bi bi-arrow-counterclockwise"></i> مرتجع
                @if($stats['returned'] > 0)<span class="count-badge">{{ $stats['returned'] }}</span>@endif
            </button>
            <button type="button" class="status-pill pill-danger" data-status="cancelled">
                <i class="bi bi-x-circle"></i> ملغي
            </button>
        </div>

        {{-- Advanced Filters --}}
        <div class="filters-bar mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small mb-1"><i class="bi bi-calendar"></i> {{ __('From Date') }}</label>
                    <input type="date" id="from" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1"><i class="bi bi-calendar"></i> {{ __('To Date') }}</label>
                    <input type="date" id="to" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1"><i class="bi bi-person"></i> {{ __('Salesman') }}</label>
                    <select id="filterSalesman" class="form-select form-select-sm">
                        <option value="">الكل</option>
                        @foreach($salesmen as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-sm btn-outline-secondary w-100" id="btnReset">
                        <i class="bi bi-arrow-clockwise"></i> إعادة تعيين الفلاتر
                    </button>
                </div>
            </div>
        </div>

        {{-- Quick Date Shortcuts --}}
        <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
            <button class="btn btn-sm btn-light date-shortcut" data-range="today">اليوم</button>
            <button class="btn btn-sm btn-light date-shortcut" data-range="yesterday">أمس</button>
            <button class="btn btn-sm btn-light date-shortcut" data-range="week">هذا الأسبوع</button>
            <button class="btn btn-sm btn-light date-shortcut" data-range="month">هذا الشهر</button>
            <span class="ms-auto small text-muted">
                إجمالي مبيعات الشهر: <strong class="text-success">{{ number_format($stats['total_month'], 2) }}</strong>
            </span>
        </div>

        {{-- DataTable --}}
        <div class="table-responsive">
            <table id="ordersTable" class="table table-hover w-100 align-middle">
                <thead>
                    <tr>
                        <th>رقم الطلب</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Salesman') }}</th>
                        <th>{{ __('Order Date') }}</th>
                        <th class="text-end">{{ __('Net Total') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* ── KPI cards ───────────────────────────────────────── */
.kpi-stat {
    background: white;
    border-radius: 14px;
    padding: 1.1rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,.04);
    transition: transform .15s, box-shadow .15s;
    height: 100%;
}
.kpi-stat:hover { transform: translateY(-3px); box-shadow: 0 6px 16px rgba(0,0,0,.08); }
.kpi-stat .kpi-icon {
    width: 56px; height: 56px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.6rem; flex-shrink: 0;
}
.kpi-stat .kpi-info small { color: #6b7280; font-size: .8rem; display: block; }
.kpi-stat .kpi-info h3 { margin: 0; font-weight: 800; color: #111827; font-size: 1.5rem; }

.kpi-primary .kpi-icon { background: #dbeafe; color: #1d4ed8; }
.kpi-success .kpi-icon { background: #d1fae5; color: #059669; }
.kpi-warning .kpi-icon { background: #fef3c7; color: #b45309; }
.kpi-info    .kpi-icon { background: #ede9fe; color: #6d28d9; }

@media (max-width: 768px) {
    .kpi-stat { padding: .85rem; gap: .65rem; }
    .kpi-stat .kpi-icon { width: 44px; height: 44px; font-size: 1.25rem; border-radius: 11px; }
    .kpi-stat .kpi-info h3 { font-size: 1.15rem; }
    .kpi-stat .kpi-info small { font-size: .72rem; }
}

/* ── Status filter pills ─────────────────────────────── */
.status-pills .status-pill {
    background: white;
    border: 1.5px solid #e5e7eb;
    color: #6b7280;
    padding: .45rem 1rem;
    border-radius: 999px;
    font-weight: 600;
    font-size: .85rem;
    transition: all .15s;
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    cursor: pointer;
}
.status-pills .status-pill:hover { color: #1f2937; border-color: #cbd5e1; }
.status-pills .status-pill.active {
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: white;
    border-color: transparent;
    box-shadow: 0 4px 10px rgba(99,102,241,.3);
}
.status-pills .status-pill.active.pill-warning { background: linear-gradient(135deg, #f59e0b, #d97706); box-shadow: 0 4px 10px rgba(245,158,11,.3); }
.status-pills .status-pill.active.pill-info    { background: linear-gradient(135deg, #06b6d4, #0891b2); box-shadow: 0 4px 10px rgba(6,182,212,.3); }
.status-pills .status-pill.active.pill-primary { background: linear-gradient(135deg, #3b82f6, #2563eb); box-shadow: 0 4px 10px rgba(59,130,246,.3); }
.status-pills .status-pill.active.pill-success { background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 4px 10px rgba(16,185,129,.3); }
.status-pills .status-pill.active.pill-dark    { background: linear-gradient(135deg, #4b5563, #1f2937); box-shadow: 0 4px 10px rgba(75,85,99,.3); }
.status-pills .status-pill.active.pill-danger  { background: linear-gradient(135deg, #ef4444, #dc2626); box-shadow: 0 4px 10px rgba(239,68,68,.3); }

.count-badge {
    background: rgba(0,0,0,0.08);
    padding: .1rem .45rem;
    border-radius: 999px;
    font-size: .7rem;
    font-weight: 700;
}
.status-pill.active .count-badge { background: rgba(255,255,255,.25); color: white; }

/* ── Filters bar ─────────────────────────────────────── */
.filters-bar {
    background: #f9fafb;
    border-radius: 12px;
    padding: 1rem;
    border: 1px solid #f3f4f6;
}

/* ── Date shortcuts ──────────────────────────────────── */
.date-shortcut {
    border-radius: 8px;
    font-size: .8rem;
    padding: .35rem .85rem;
    border: 1px solid #e5e7eb;
}
.date-shortcut.active {
    background: #6366f1;
    color: white;
    border-color: #6366f1;
}

/* ── Table refinements ───────────────────────────────── */
#ordersTable thead th {
    background: #f9fafb;
    color: #4b5563;
    font-weight: 700;
    font-size: .85rem;
    border-bottom: 2px solid #e5e7eb;
    padding: .85rem .65rem;
}
#ordersTable tbody td {
    padding: .85rem .65rem;
    vertical-align: middle;
    border-bottom: 1px solid #f3f4f6;
}
#ordersTable tbody tr:hover { background: #fafbff; }
#ordersTable .badge { font-size: .72rem; padding: .35em .65em; }

.dataTables_wrapper .dt-buttons { margin-bottom: .5rem; }
.dataTables_wrapper .dt-buttons .btn { margin-left: .25rem; }

@media (max-width: 576px) {
    .status-pills .status-pill { font-size: .75rem; padding: .35rem .7rem; }
}
</style>
@endpush

@push('scripts')
<script>
$(function () {
    let currentStatus = '';

    const table = $('#ordersTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        order: [[3, 'desc']],
        pageLength: 25,
        ajax: {
            url: "{{ route('orders.data') }}",
            data: d => Object.assign(d, {
                from: $('#from').val(),
                to: $('#to').val(),
                status: currentStatus,
                salesman_id: $('#filterSalesman').val()
            })
        },
        columns: [
            { data: 'order_number',  name: 'order_number' },
            { data: 'customer_name', name: 'customer.name' },
            { data: 'salesman_name', name: 'salesman.name' },
            { data: 'order_date',    name: 'order_date' },
            { data: 'net_total',     name: 'net_total', className: 'text-end' },
            { data: 'status_badge',  name: 'status', orderable: false },
            { data: 'actions',       name: 'actions', orderable: false, searchable: false, className: 'text-center' }
        ],
        dom: 'Bfrtip',
        buttons: [
            { extend: 'excel', text: '<i class="bi bi-file-earmark-excel"></i> Excel', className: 'btn btn-sm btn-success' },
            { extend: 'print', text: '<i class="bi bi-printer"></i> طباعة',           className: 'btn btn-sm btn-info' },
        ],
        language: window.dtArabic
    });

    // Status pills
    $('.status-pill').on('click', function () {
        $('.status-pill').removeClass('active');
        $(this).addClass('active');
        currentStatus = $(this).data('status') || '';
        table.ajax.reload();
    });

    // Filters
    $('#from, #to, #filterSalesman').on('change', () => table.ajax.reload());

    // Date shortcuts
    $('.date-shortcut').on('click', function () {
        $('.date-shortcut').removeClass('active');
        $(this).addClass('active');

        const range = $(this).data('range');
        const today = new Date();
        const fmt   = d => d.toISOString().split('T')[0];
        let from = '', to = '';

        if (range === 'today') {
            from = to = fmt(today);
        } else if (range === 'yesterday') {
            const y = new Date(today); y.setDate(y.getDate() - 1);
            from = to = fmt(y);
        } else if (range === 'week') {
            const start = new Date(today);
            start.setDate(start.getDate() - start.getDay());
            from = fmt(start); to = fmt(today);
        } else if (range === 'month') {
            from = fmt(new Date(today.getFullYear(), today.getMonth(), 1));
            to   = fmt(today);
        }

        $('#from').val(from);
        $('#to').val(to);
        table.ajax.reload();
    });

    // Reset
    $('#btnReset').on('click', () => {
        $('#from, #to, #filterSalesman').val('');
        $('.status-pill').removeClass('active');
        $('.status-pill[data-status=""]').addClass('active');
        $('.date-shortcut').removeClass('active');
        currentStatus = '';
        table.ajax.reload();
    });
});
</script>
@endpush
