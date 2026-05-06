@extends('layouts.admin')
@section('title', __('Products'))
@section('page_title', __('Products'))

@section('content')

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-stat kpi-primary">
            <div class="kpi-icon"><i class="bi bi-box-seam"></i></div>
            <div class="kpi-info"><small>إجمالي المنتجات</small><h3>{{ $stats['total'] }}</h3></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-stat kpi-success">
            <div class="kpi-icon"><i class="bi bi-check-circle"></i></div>
            <div class="kpi-info"><small>نشطة</small><h3>{{ $stats['active'] }}</h3></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-stat kpi-warning">
            <div class="kpi-icon"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="kpi-info"><small>مخزون منخفض</small><h3>{{ $stats['low_stock'] }}</h3></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-stat kpi-info">
            <div class="kpi-icon"><i class="bi bi-cash-stack"></i></div>
            <div class="kpi-info"><small>قيمة المخزون</small><h3>{{ number_format($stats['stock_value'], 0) }}</h3></div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
        <div>
            <h5 class="mb-0 fw-bold"><i class="bi bi-box-seam text-primary"></i> {{ __('Products') }}</h5>
            <small class="text-muted">إدارة المنتجات والأسعار</small>
        </div>
        <button class="btn btn-primary" id="btnAdd"><i class="bi bi-plus-lg"></i> منتج جديد</button>
    </div>
    <div class="card-body">

        <div class="d-flex flex-wrap gap-2 mb-3 filter-pills">
            <button type="button" class="filter-pill active" data-status=""><i class="bi bi-grid-fill"></i> الكل <span class="count-badge">{{ $stats['total'] }}</span></button>
            <button type="button" class="filter-pill pill-success" data-status="active"><i class="bi bi-check-circle"></i> نشطة <span class="count-badge">{{ $stats['active'] }}</span></button>
            <button type="button" class="filter-pill pill-dark" data-status="inactive"><i class="bi bi-x-circle"></i> غير نشطة <span class="count-badge">{{ $stats['inactive'] }}</span></button>
        </div>

        <div class="filters-bar mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label small mb-1"><i class="bi bi-tags"></i> {{ __('Category') }}</label>
                    <select id="filterCategory" class="form-select form-select-sm">
                        <option value="">كل التصنيفات</option>
                        @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-6"><button class="btn btn-sm btn-outline-secondary w-100" id="btnReset"><i class="bi bi-arrow-clockwise"></i> إعادة تعيين</button></div>
            </div>
        </div>

        <div class="table-responsive">
            <table id="dt" class="table pretty-table table-hover w-100 align-middle">
                <thead><tr>
                    <th>{{ __('Code') }}</th><th>{{ __('Name') }}</th><th>{{ __('Category') }}</th>
                    <th>{{ __('Unit') }}</th><th class="text-end">{{ __('Price') }}</th>
                    <th class="text-end">{{ __('Cost') }}</th><th>المخزون</th>
                    <th>{{ __('Status') }}</th><th class="text-center">{{ __('Actions') }}</th>
                </tr></thead>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="formModal">
    <div class="modal-dialog modal-lg">
        <form id="form" class="modal-content">
            @csrf
            <div class="modal-header"><h6 class="modal-title">{{ __('Product') }}</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="id" id="id">
                <div class="row g-3">
                    <div class="col-md-4"><label>{{ __('Code') }} *</label><input type="text" name="code" id="code" class="form-control" required></div>
                    <div class="col-md-8"><label>{{ __('Name') }} *</label><input type="text" name="name" id="name" class="form-control" required></div>
                    <div class="col-md-6"><label>{{ __('Category') }}</label>
                        <select name="category_id" id="category_id" class="form-select"><option value="">--</option>
                            @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-6"><label>{{ __('Unit') }}</label><input type="text" name="unit" id="unit" class="form-control" value="علبة"></div>
                    <div class="col-md-4"><label>{{ __('Price') }} *</label><input type="number" step="0.01" name="price" id="price" class="form-control" required></div>
                    <div class="col-md-4"><label>{{ __('Cost') }}</label><input type="number" step="0.01" name="cost" id="cost" class="form-control"></div>
                    <div class="col-md-4"><label>الحد الأدنى</label><input type="number" name="min_stock" id="min_stock" class="form-control"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    let currentStatus = '';
    const t = $('#dt').DataTable({
        processing: true, serverSide: true, responsive: true, pageLength: 25,
        ajax: { url: "{{ route('products.data') }}", data: function (d) {
            d.category_id = $('#filterCategory').val();
            d.status = currentStatus;
        }},
        columns: [
            { data: 'code',          name: 'code' },
            { data: 'name',          name: 'name' },
            { data: 'category_name', name: 'category.name' },
            { data: 'unit',          name: 'unit' },
            { data: 'price',         name: 'price', className: 'text-end' },
            { data: 'cost',          name: 'cost', className: 'text-end' },
            { data: 'total_stock',   name: 'total_stock', orderable: false, searchable: false },
            { data: 'status_badge',  name: 'is_active', orderable: false },
            { data: 'actions',       name: 'actions', orderable: false, searchable: false, className: 'text-center' }
        ],
        dom: 'Bfrtip',
        buttons: [
            { extend: 'excel', text: '<i class="bi bi-file-earmark-excel"></i> Excel', className: 'btn btn-sm btn-success' },
            { extend: 'print', text: '<i class="bi bi-printer"></i> طباعة',           className: 'btn btn-sm btn-info' },
        ]
    });

    $('.filter-pill').on('click', function () {
        $('.filter-pill').removeClass('active'); $(this).addClass('active');
        currentStatus = $(this).data('status') || '';
        t.ajax.reload();
    });
    $('#filterCategory').on('change', () => t.ajax.reload());
    $('#btnReset').on('click', () => {
        $('#filterCategory').val('');
        $('.filter-pill').removeClass('active');
        $('.filter-pill[data-status=""]').addClass('active');
        currentStatus = '';
        t.ajax.reload();
    });

    $('#btnAdd').on('click', () => { $('#form')[0].reset(); $('#id').val(''); $('#formModal').modal('show'); });

    $('#dt').on('click', '.btn-edit', function () {
        $.get("{{ url('products') }}/" + $(this).data('id') + "/edit", d => {
            Object.entries(d).forEach(([k,v]) => $(`#${k}`).val(v));
            $('#formModal').modal('show');
        });
    });

    $('#dt').on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        Swal.fire({ title: '{{ __("Are you sure?") }}', icon: 'warning', showCancelButton: true })
            .then(r => r.isConfirmed && $.ajax({ url: "{{ url('products') }}/" + id, type: 'DELETE',
                success: () => { toastr.success('{{ __("Deleted successfully") }}'); t.ajax.reload(); }}));
    });

    $('#form').on('submit', function (e) {
        e.preventDefault();
        const id = $('#id').val();
        const url = id ? "{{ url('products') }}/" + id : "{{ route('products.store') }}";
        const method = id ? 'PUT' : 'POST';
        $.ajax({ url, method, data: $(this).serialize(),
            success: r => { $('#formModal').modal('hide'); toastr.success(r.message); t.ajax.reload(); },
            error: () => toastr.error('خطأ في الحفظ')
        });
    });
});
</script>
@endpush
