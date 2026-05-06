@extends('layouts.admin')
@section('title', __('Stock'))
@section('page_title', __('Stock'))

@section('content')

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-stat kpi-primary">
            <div class="kpi-icon"><i class="bi bi-stack"></i></div>
            <div class="kpi-info"><small>إجمالي الكميات</small><h3>{{ number_format($stats['total_units']) }}</h3></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-stat kpi-warning">
            <div class="kpi-icon"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="kpi-info"><small>مخزون منخفض</small><h3>{{ $stats['low_stock'] }}</h3></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-stat kpi-danger">
            <div class="kpi-icon"><i class="bi bi-x-circle"></i></div>
            <div class="kpi-info"><small>نافد</small><h3>{{ $stats['out_of_stock'] }}</h3></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-stat kpi-success">
            <div class="kpi-icon"><i class="bi bi-cash-coin"></i></div>
            <div class="kpi-info"><small>قيمة المخزون</small><h3>{{ number_format($stats['stock_value'], 0) }}</h3></div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
        <div>
            <h5 class="mb-0 fw-bold"><i class="bi bi-stack text-primary"></i> {{ __('Stock') }}</h5>
            <small class="text-muted">{{ $stats['movements_today'] }} حركة مخزون اليوم</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('stock.inventory') }}" class="btn btn-info btn-sm"><i class="bi bi-clipboard-check"></i> جرد</a>
            <button class="btn btn-warning btn-sm" id="btnAdjust"><i class="bi bi-pencil-square"></i> تعديل</button>
            <button class="btn btn-primary btn-sm" id="btnTransfer"><i class="bi bi-arrow-left-right"></i> تحويل</button>
        </div>
    </div>
    <div class="card-body">

        <div class="filters-bar mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label small mb-1"><i class="bi bi-building"></i> {{ __('Warehouse') }}</label>
                    <select id="filterWarehouse" class="form-select form-select-sm">
                        <option value="">كل المستودعات</option>
                        @foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-6"><button class="btn btn-sm btn-outline-secondary w-100" id="btnReset"><i class="bi bi-arrow-clockwise"></i> إعادة تعيين</button></div>
            </div>
        </div>

        <div class="table-responsive">
            <table id="dt" class="table pretty-table table-hover w-100 align-middle">
                <thead><tr>
                    <th>{{ __('Code') }}</th><th>{{ __('Product') }}</th><th>{{ __('Warehouse') }}</th>
                    <th class="text-end">الكمية</th><th class="text-end">محجوز</th>
                    <th class="text-end">متاح</th><th>{{ __('Status') }}</th>
                </tr></thead>
            </table>
        </div>
    </div>
</div>

@include('stock._adjust_modal', ['warehouses' => $warehouses, 'products' => $products])
@include('stock._transfer_modal', ['warehouses' => $warehouses, 'products' => $products])
@endsection

@push('scripts')
<script>
const t = $('#dt').DataTable({
    processing: true, serverSide: true, responsive: true, pageLength: 25,
    ajax: { url: "{{ route('stock.data') }}", data: d => Object.assign(d, { warehouse_id: $('#filterWarehouse').val() }) },
    columns: [
        { data: 'product_code',   name: 'product.code' },
        { data: 'product_name',   name: 'product.name' },
        { data: 'warehouse_name', name: 'warehouse.name' },
        { data: 'quantity',       name: 'quantity', className: 'text-end' },
        { data: 'reserved',       name: 'reserved', className: 'text-end' },
        { data: 'available',      orderable: false, searchable: false, className: 'text-end' },
        { data: 'status',         orderable: false, searchable: false }
    ],
    dom: 'Bfrtip',
    buttons: [
        { extend: 'excel', text: '<i class="bi bi-file-earmark-excel"></i> Excel', className: 'btn btn-sm btn-success' },
        { extend: 'print', text: '<i class="bi bi-printer"></i> طباعة',           className: 'btn btn-sm btn-info' },
    ]
});

$('#filterWarehouse').on('change', () => t.ajax.reload());
$('#btnReset').on('click', () => { $('#filterWarehouse').val(''); t.ajax.reload(); });
$('#btnAdjust').on('click', () => $('#adjustModal').modal('show'));
$('#btnTransfer').on('click', () => $('#transferModal').modal('show'));

$('#adjustForm').on('submit', function (e) {
    e.preventDefault();
    $.post("{{ route('stock.adjust') }}", $(this).serialize())
        .done(r => { $('#adjustModal').modal('hide'); toastr.success(r.message); t.ajax.reload(); })
        .fail(x => toastr.error(x.responseJSON?.message || 'خطأ'));
});

$('#transferForm').on('submit', function (e) {
    e.preventDefault();
    $.post("{{ route('stock.transfer') }}", $(this).serialize())
        .done(r => { $('#transferModal').modal('hide'); toastr.success(r.message); t.ajax.reload(); })
        .fail(x => toastr.error(x.responseJSON?.message || 'خطأ'));
});
</script>
@endpush
