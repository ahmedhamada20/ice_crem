@extends('layouts.admin')
@section('title', __('Stock'))
@section('page_title', __('Stock'))

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">{{ __('Stock') }}</h5>
        <div>
            <button class="btn btn-warning btn-sm" id="btnAdjust"><i class="bi bi-pencil-square"></i> جرد</button>
            <button class="btn btn-info btn-sm" id="btnTransfer"><i class="bi bi-arrow-left-right"></i> تحويل</button>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-2 mb-3">
            <div class="col-md-3">
                <select id="filterWarehouse" class="form-select form-select-sm">
                    <option value="">{{ __('Warehouse') }} - {{ __('All') }}</option>
                    @foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach
                </select>
            </div>
        </div>
        <table id="dt" class="table table-striped w-100">
            <thead class="table-dark">
                <tr><th>{{ __('Code') }}</th><th>{{ __('Product') }}</th><th>{{ __('Warehouse') }}</th><th>الكمية</th><th>محجوز</th><th>متاح</th><th>{{ __('Status') }}</th></tr>
            </thead>
        </table>
    </div>
</div>

@php $allWarehouses = $warehouses; $allProducts = $products; @endphp
@include('stock._adjust_modal', ['warehouses' => $allWarehouses, 'products' => $allProducts])
@include('stock._transfer_modal', ['warehouses' => $allWarehouses, 'products' => $allProducts])
@endsection

@push('scripts')
<script>
const t = $('#dt').DataTable({ processing: true, serverSide: true,
    ajax: { url: "{{ route('stock.data') }}", data: d => Object.assign(d, { warehouse_id: $('#filterWarehouse').val() }) },
    columns: [{ data: 'product_code' }, { data: 'product_name' }, { data: 'warehouse_name' },
              { data: 'quantity', className: 'text-end' }, { data: 'reserved', className: 'text-end' },
              { data: 'available', className: 'text-end' }, { data: 'status' }],
    language: window.dtArabic });

$('#filterWarehouse').on('change', () => t.ajax.reload());
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
