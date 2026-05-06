@extends('layouts.admin')
@section('title', __('Deliveries'))
@section('page_title', __('Deliveries'))

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0"><i class="bi bi-truck"></i> {{ __('Deliveries') }}</h5>
        <div>
            <a href="{{ route('deliveries.dispatch') }}" class="btn btn-sm btn-primary"><i class="bi bi-send"></i> توزيع الطلبات</a>
            <a href="{{ route('deliveries.map') }}" class="btn btn-sm btn-info"><i class="bi bi-map"></i> خريطة المناديب</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-2 mb-3">
            <div class="col-md-3">
                <select id="filterStatus" class="form-select form-select-sm">
                    <option value="">{{ __('Status') }} - {{ __('All') }}</option>
                    <option value="assigned">معين</option>
                    <option value="in_progress">قيد التنفيذ</option>
                    <option value="delivered">{{ __('Delivered') }}</option>
                    <option value="failed">فشل التوصيل</option>
                </select>
            </div>
        </div>
        <table id="dt" class="table table-striped w-100">
            <thead class="table-dark">
                <tr><th>رقم التوصيلة</th><th>رقم الطلب</th><th>{{ __('Customer') }}</th><th>{{ __('Driver') }}</th><th>تاريخ التعيين</th><th>الحالة</th><th>{{ __('Actions') }}</th></tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    const t = $('#dt').DataTable({
        processing: true, serverSide: true, responsive: true,
        ajax: { url: "{{ route('deliveries.data') }}", data: d => Object.assign(d, { status: $('#filterStatus').val() }) },
        columns: [
            { data: 'delivery_number' },
            { data: 'order_number' },
            { data: 'customer_name' },
            { data: 'driver_name' },
            { data: 'assigned_at' },
            { data: 'status' },
            { data: 'actions', orderable: false, searchable: false }
        ],
        language: window.dtArabic
    });
    $('#filterStatus').on('change', () => t.ajax.reload());
});
</script>
@endpush
