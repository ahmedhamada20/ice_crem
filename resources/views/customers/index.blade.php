@extends('layouts.admin')

@section('title', __('Customers'))
@section('page_title', __('Customers'))

@section('content')

{{-- ═══════════════════════════════════════════════════════════
     KPI Stats Cards
     ═══════════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-stat kpi-primary">
            <div class="kpi-icon"><i class="bi bi-people-fill"></i></div>
            <div class="kpi-info">
                <small>إجمالي العملاء</small>
                <h3>{{ $stats['total'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-stat kpi-success">
            <div class="kpi-icon"><i class="bi bi-check-circle-fill"></i></div>
            <div class="kpi-info">
                <small>عملاء نشطون</small>
                <h3>{{ $stats['active'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-stat kpi-warning">
            <div class="kpi-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div class="kpi-info">
                <small>عملاء عليهم رصيد</small>
                <h3>{{ $stats['with_balance'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-stat kpi-danger">
            <div class="kpi-icon"><i class="bi bi-cash-stack"></i></div>
            <div class="kpi-info">
                <small>إجمالي المديونية</small>
                <h3>{{ number_format($stats['total_balance'], 0) }}</h3>
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
            <h5 class="mb-0 fw-bold"><i class="bi bi-people text-primary"></i> {{ __('Customers') }}</h5>
            <small class="text-muted">إدارة العملاء وكشوف الحسابات</small>
        </div>
        @can('create', App\Models\Customer::class)
            <button class="btn btn-primary" id="btnAddCustomer">
                <i class="bi bi-plus-lg"></i> عميل جديد
            </button>
        @endcan
    </div>

    <div class="card-body">

        {{-- Type Filter Pills --}}
        <div class="d-flex flex-wrap gap-2 mb-3 type-pills">
            <button type="button" class="type-pill active" data-type="">
                <i class="bi bi-grid-fill"></i> الكل
                <span class="count-badge">{{ $stats['total'] }}</span>
            </button>
            <button type="button" class="type-pill pill-primary" data-type="shop">
                <i class="bi bi-shop"></i> محلات
                <span class="count-badge">{{ $stats['shops'] }}</span>
            </button>
            <button type="button" class="type-pill pill-success" data-type="supermarket">
                <i class="bi bi-basket"></i> سوبر ماركت
                <span class="count-badge">{{ $stats['supermarkets'] }}</span>
            </button>
            <button type="button" class="type-pill pill-info" data-type="cafe">
                <i class="bi bi-cup-hot"></i> كافيهات
                <span class="count-badge">{{ $stats['cafes'] }}</span>
            </button>
        </div>

        {{-- Filters --}}
        <div class="filters-bar mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small mb-1"><i class="bi bi-map"></i> {{ __('Zone') }}</label>
                    <select id="filterZone" class="form-select form-select-sm">
                        <option value="">كل المناطق</option>
                        @foreach($zones as $zone)
                            <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small mb-1"><i class="bi bi-shield-check"></i> {{ __('Status') }}</label>
                    <select id="filterStatus" class="form-select form-select-sm">
                        <option value="">كل الحالات</option>
                        <option value="active">{{ __('Active') }}</option>
                        <option value="inactive">{{ __('Inactive') }}</option>
                        <option value="blocked">محظور</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-sm btn-outline-secondary w-100" id="btnReset">
                        <i class="bi bi-arrow-clockwise"></i> إعادة تعيين الفلاتر
                    </button>
                </div>
            </div>
        </div>

        {{-- DataTable --}}
        <div class="table-responsive">
            <table id="customersTable" class="table table-hover w-100 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('Code') }}</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Zone') }}</th>
                        <th class="text-end">{{ __('Credit Limit') }}</th>
                        <th class="text-end">{{ __('Balance') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

{{-- Modal for Add/Edit --}}
@include('customers._form')

@endsection

@push('styles')
<style>
/* ── KPI cards (shared with orders) ──────────────── */
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

/* ── Type filter pills ──────────────────────────── */
.type-pills .type-pill {
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
.type-pills .type-pill:hover { color: #1f2937; border-color: #cbd5e1; }
.type-pills .type-pill.active {
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: white;
    border-color: transparent;
    box-shadow: 0 4px 10px rgba(99,102,241,.3);
}
.type-pills .type-pill.active.pill-primary { background: linear-gradient(135deg, #3b82f6, #2563eb); box-shadow: 0 4px 10px rgba(59,130,246,.3); }
.type-pills .type-pill.active.pill-success { background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 4px 10px rgba(16,185,129,.3); }
.type-pills .type-pill.active.pill-info    { background: linear-gradient(135deg, #06b6d4, #0891b2); box-shadow: 0 4px 10px rgba(6,182,212,.3); }

.count-badge {
    background: rgba(0,0,0,0.08);
    padding: .1rem .45rem;
    border-radius: 999px;
    font-size: .7rem;
    font-weight: 700;
}
.type-pill.active .count-badge { background: rgba(255,255,255,.25); color: white; }

/* ── Filters bar ────────────────────────────────── */
.filters-bar {
    background: #f9fafb;
    border-radius: 12px;
    padding: 1rem;
    border: 1px solid #f3f4f6;
}

/* ── Table refinements ──────────────────────────── */
#customersTable thead th {
    background: #f9fafb;
    color: #4b5563;
    font-weight: 700;
    font-size: .85rem;
    border-bottom: 2px solid #e5e7eb;
    padding: .85rem .65rem;
}
#customersTable tbody td {
    padding: .85rem .65rem;
    vertical-align: middle;
    border-bottom: 1px solid #f3f4f6;
}
#customersTable tbody tr:hover { background: #fafbff; }
#customersTable .badge { font-size: .72rem; padding: .35em .65em; }

@media (max-width: 576px) {
    .type-pills .type-pill { font-size: .75rem; padding: .35rem .7rem; }
}
</style>
@endpush

@push('scripts')
<script>
$(function () {
    let currentType = '';

    const table = $('#customersTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        order: [[0, 'desc']],
        pageLength: 25,
        ajax: {
            url: "{{ route('customers.data') }}",
            data: function (d) {
                d.zone_id = $('#filterZone').val();
                d.type    = currentType;
                d.status  = $('#filterStatus').val();
            }
        },
        columns: [
            { data: 'code',         name: 'code' },
            { data: 'name',         name: 'name' },
            { data: 'type',         name: 'type' },
            { data: 'zone_name',    name: 'zone.name' },
            { data: 'credit_limit', name: 'credit_limit', className: 'text-end' },
            { data: 'balance',      name: 'balance',      className: 'text-end' },
            { data: 'status_badge', name: 'status', orderable: false },
            { data: 'actions',      name: 'actions', orderable: false, searchable: false, className: 'text-center' }
        ],
        dom: 'Bfrtip',
        buttons: [
            { extend: 'excel', text: '<i class="bi bi-file-earmark-excel"></i> Excel', className: 'btn btn-sm btn-success' },
            { extend: 'print', text: '<i class="bi bi-printer"></i> طباعة',           className: 'btn btn-sm btn-info' },
        ]
    });

    // Type filter pills
    $('.type-pill').on('click', function () {
        $('.type-pill').removeClass('active');
        $(this).addClass('active');
        currentType = $(this).data('type') || '';
        table.ajax.reload();
    });

    $('#filterZone, #filterStatus').on('change', () => table.ajax.reload());

    $('#btnReset').on('click', () => {
        $('#filterZone, #filterStatus').val('');
        $('.type-pill').removeClass('active');
        $('.type-pill[data-type=""]').addClass('active');
        currentType = '';
        table.ajax.reload();
    });

    // Add new
    $('#btnAddCustomer').on('click', function () {
        $('#customerForm')[0].reset();
        $('#customer_id').val('');
        $('#customerModalLabel').text("{{ __('Add New') }} - {{ __('Customer') }}");
        $('#customerModal').modal('show');
    });

    // Edit
    $('#customersTable').on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        $.get("{{ url('customers') }}/" + id + "/edit", function (data) {
            $('#customer_id').val(data.id);
            $('#name').val(data.name);
            $('#code').val(data.code);
            $('#phone').val(data.phone);
            $('#alt_phone').val(data.alt_phone);
            $('#email').val(data.email);
            $('#address').val(data.address);
            $('#zone_id').val(data.zone_id).trigger('change');
            $('#type').val(data.type);
            $('#credit_limit').val(data.credit_limit);
            $('#location_lat').val(data.location_lat);
            $('#location_lng').val(data.location_lng);
            $('#contact_person').val(data.contact_person);
            $('#notes').val(data.notes);
            $('#status').val(data.status);
            $('#customerModalLabel').text("{{ __('Edit') }} - " + data.name);
            $('#customerModal').modal('show');
        });
    });

    // Delete
    $('#customersTable').on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: "{{ __('Are you sure?') }}",
            text: "{{ __('This action cannot be undone') }}",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: "{{ __('Yes') }}",
            cancelButtonText: "{{ __('Cancel') }}"
        }).then(r => {
            if (r.isConfirmed) {
                $.ajax({
                    url: "{{ url('customers') }}/" + id,
                    type: 'DELETE',
                    success: () => { toastr.success("{{ __('Deleted successfully') }}"); table.ajax.reload(); },
                    error: () => toastr.error("{{ __('Operation failed') }}")
                });
            }
        });
    });

    // Save
    $('#customerForm').on('submit', function (e) {
        e.preventDefault();
        const id = $('#customer_id').val();
        const url = id ? "{{ url('customers') }}/" + id : "{{ route('customers.store') }}";
        const method = id ? 'PUT' : 'POST';

        $.ajax({
            url, method,
            data: $(this).serialize(),
            success: function (resp) {
                $('#customerModal').modal('hide');
                toastr.success(resp.message);
                table.ajax.reload();
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    let msg = '';
                    Object.values(xhr.responseJSON.errors).forEach(e => msg += e.join('<br>') + '<br>');
                    toastr.error(msg);
                } else {
                    toastr.error("{{ __('Operation failed') }}");
                }
            }
        });
    });

    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({ theme: 'bootstrap-5', dropdownParent: $('#customerModal') });
    }
});
</script>
@endpush
