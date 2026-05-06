@extends('layouts.admin')
@section('title', __('Deliveries'))
@section('page_title', __('Deliveries'))

@section('content')

{{-- ═══════════════════════════════════════════════════════════
     KPI Stats Cards
     ═══════════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-stat kpi-warning">
            <div class="kpi-icon"><i class="bi bi-clock-history"></i></div>
            <div class="kpi-info">
                <small>معيّنة</small>
                <h3>{{ $stats['assigned'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-stat kpi-primary">
            <div class="kpi-icon"><i class="bi bi-truck"></i></div>
            <div class="kpi-info">
                <small>قيد التنفيذ</small>
                <h3>{{ $stats['in_progress'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-stat kpi-success">
            <div class="kpi-icon"><i class="bi bi-check2-all"></i></div>
            <div class="kpi-info">
                <small>تم التسليم اليوم</small>
                <h3>{{ $stats['delivered'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-stat kpi-danger">
            <div class="kpi-icon"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="kpi-info">
                <small>فشل اليوم</small>
                <h3>{{ $stats['failed'] }}</h3>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     Quick action cards (admin only)
     ═══════════════════════════════════════════════════════════ --}}
@hasanyrole('super-admin|admin')
@if($stats['pending_orders'] > 0)
<div class="alert alert-warning d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 border-0 shadow-sm">
    <div>
        <i class="bi bi-bell-fill"></i>
        <strong>{{ $stats['pending_orders'] }}</strong> طلب مؤكد جاهز للتوزيع على السائقين
    </div>
    <a href="{{ route('deliveries.dispatch') }}" class="btn btn-warning btn-sm">
        <i class="bi bi-send"></i> توزيع الطلبات
    </a>
</div>
@endif
@endhasanyrole

{{-- ═══════════════════════════════════════════════════════════
     Main Card
     ═══════════════════════════════════════════════════════════ --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
        <div>
            <h5 class="mb-0 fw-bold"><i class="bi bi-truck text-primary"></i> {{ __('Deliveries') }}</h5>
            <small class="text-muted">{{ $stats['active_drivers'] }} سائق نشط — متابعة وإدارة كل التوصيلات</small>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @hasanyrole('super-admin|admin')
            <a href="{{ route('deliveries.dispatch') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-send"></i> توزيع طلبات
            </a>
            <a href="{{ route('deliveries.map') }}" class="btn btn-info btn-sm">
                <i class="bi bi-map"></i> الخريطة
            </a>
            @endhasanyrole
        </div>
    </div>

    <div class="card-body">

        {{-- Status Filter Pills --}}
        <div class="d-flex flex-wrap gap-2 mb-3 status-pills">
            <button type="button" class="status-pill active" data-status="">
                <i class="bi bi-grid-fill"></i> الكل
                <span class="count-badge">{{ $stats['total'] }}</span>
            </button>
            <button type="button" class="status-pill pill-warning" data-status="assigned">
                <i class="bi bi-clock"></i> معيّن
                @if($stats['assigned'] > 0)<span class="count-badge">{{ $stats['assigned'] }}</span>@endif
            </button>
            <button type="button" class="status-pill pill-primary" data-status="in_progress">
                <i class="bi bi-truck"></i> قيد التنفيذ
                @if($stats['in_progress'] > 0)<span class="count-badge">{{ $stats['in_progress'] }}</span>@endif
            </button>
            <button type="button" class="status-pill pill-success" data-status="delivered">
                <i class="bi bi-check2-all"></i> تم التسليم
            </button>
            <button type="button" class="status-pill pill-danger" data-status="failed">
                <i class="bi bi-x-circle"></i> فشل
            </button>
            <button type="button" class="status-pill pill-dark" data-status="returned">
                <i class="bi bi-arrow-counterclockwise"></i> مرتجع
                @if($stats['returned'] > 0)<span class="count-badge">{{ $stats['returned'] }}</span>@endif
            </button>
        </div>

        {{-- Filters --}}
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
                @hasanyrole('super-admin|admin')
                <div class="col-md-3">
                    <label class="form-label small mb-1"><i class="bi bi-person"></i> السائق</label>
                    <select id="filterDriver" class="form-select form-select-sm">
                        <option value="">كل السائقين</option>
                        @foreach($drivers as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endhasanyrole
                <div class="col-md-3">
                    <button class="btn btn-sm btn-outline-secondary w-100" id="btnReset">
                        <i class="bi bi-arrow-clockwise"></i> إعادة تعيين الفلاتر
                    </button>
                </div>
            </div>
        </div>

        {{-- Quick Date Shortcuts --}}
        <div class="d-flex flex-wrap gap-2 mb-3">
            <button class="btn btn-sm btn-light date-shortcut" data-range="today">اليوم</button>
            <button class="btn btn-sm btn-light date-shortcut" data-range="yesterday">أمس</button>
            <button class="btn btn-sm btn-light date-shortcut" data-range="week">هذا الأسبوع</button>
            <button class="btn btn-sm btn-light date-shortcut" data-range="month">هذا الشهر</button>
        </div>

        {{-- DataTable --}}
        <div class="table-responsive">
            <table id="dt" class="table table-hover w-100 align-middle">
                <thead>
                    <tr>
                        <th>رقم التوصيلة</th>
                        <th>رقم الطلب</th>
                        <th>{{ __('Customer') }}</th>
                        <th>السائق</th>
                        <th>تاريخ التعيين</th>
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
.kpi-danger  .kpi-icon { background: #fee2e2; color: #b91c1c; }

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
.status-pills .status-pill.active.pill-primary { background: linear-gradient(135deg, #3b82f6, #2563eb); box-shadow: 0 4px 10px rgba(59,130,246,.3); }
.status-pills .status-pill.active.pill-success { background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 4px 10px rgba(16,185,129,.3); }
.status-pills .status-pill.active.pill-danger  { background: linear-gradient(135deg, #ef4444, #dc2626); box-shadow: 0 4px 10px rgba(239,68,68,.3); }
.status-pills .status-pill.active.pill-dark    { background: linear-gradient(135deg, #4b5563, #1f2937); box-shadow: 0 4px 10px rgba(75,85,99,.3); }

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

/* ── Table refinements ───────────────────────────────── */
#dt thead th {
    background: #f9fafb;
    color: #4b5563;
    font-weight: 700;
    font-size: .85rem;
    border-bottom: 2px solid #e5e7eb;
    padding: .85rem .65rem;
}
#dt tbody td {
    padding: .85rem .65rem;
    vertical-align: middle;
    border-bottom: 1px solid #f3f4f6;
}
#dt tbody tr:hover { background: #fafbff; }
#dt .badge { font-size: .72rem; padding: .35em .65em; }

@media (max-width: 576px) {
    .status-pills .status-pill { font-size: .75rem; padding: .35rem .7rem; }
}
</style>
@endpush

@push('scripts')
<script>
$(function () {
    let currentStatus = '';

    const table = $('#dt').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        order: [[4, 'desc']],
        pageLength: 25,
        ajax: {
            url: "{{ route('deliveries.data') }}",
            data: function (d) {
                d.status    = currentStatus;
                d.driver_id = $('#filterDriver').val();
                d.from      = $('#from').val();
                d.to        = $('#to').val();
            }
        },
        columns: [
            { data: 'delivery_number', name: 'delivery_number' },
            { data: 'order_number',    name: 'order.order_number' },
            { data: 'customer_name',   name: 'order.customer.name' },
            { data: 'driver_name',     name: 'driver.name' },
            { data: 'assigned_at',     name: 'assigned_at' },
            { data: 'status_badge',    name: 'status', orderable: false },
            { data: 'actions',         name: 'actions', orderable: false, searchable: false, className: 'text-center' }
        ],
        dom: 'Bfrtip',
        buttons: [
            { extend: 'excel', text: '<i class="bi bi-file-earmark-excel"></i> Excel', className: 'btn btn-sm btn-success' },
            { extend: 'print', text: '<i class="bi bi-printer"></i> طباعة',           className: 'btn btn-sm btn-info' },
        ]
    });

    // Status pills
    $('.status-pill').on('click', function () {
        $('.status-pill').removeClass('active');
        $(this).addClass('active');
        currentStatus = $(this).data('status') || '';
        table.ajax.reload();
    });

    // Filters
    $('#from, #to, #filterDriver').on('change', () => table.ajax.reload());

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
            const start = new Date(today); start.setDate(start.getDate() - start.getDay());
            from = fmt(start); to = fmt(today);
        } else if (range === 'month') {
            from = fmt(new Date(today.getFullYear(), today.getMonth(), 1));
            to   = fmt(today);
        }

        $('#from').val(from);
        $('#to').val(to);
        table.ajax.reload();
    });

    $('#btnReset').on('click', () => {
        $('#from, #to, #filterDriver').val('');
        $('.status-pill').removeClass('active');
        $('.status-pill[data-status=""]').addClass('active');
        $('.date-shortcut').removeClass('active');
        currentStatus = '';
        table.ajax.reload();
    });
});
</script>
@endpush
