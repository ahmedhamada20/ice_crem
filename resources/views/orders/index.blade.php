@extends('layouts.admin')
@section('title', __('Orders'))
@section('page_title', __('Orders'))

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-cart"></i> {{ __('Orders') }}</h5>
        @can('create', App\Models\Order::class)
        <a href="{{ route('orders.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> {{ __('Add New') }}</a>
        @endcan
    </div>
    <div class="card-body">
        <div class="row g-2 mb-3">
            <div class="col-md-2">
                <input type="date" id="from" class="form-control form-control-sm" placeholder="{{ __('From Date') }}">
            </div>
            <div class="col-md-2">
                <input type="date" id="to" class="form-control form-control-sm" placeholder="{{ __('To Date') }}">
            </div>
            <div class="col-md-2">
                <select id="filterStatus" class="form-select form-select-sm">
                    <option value="">{{ __('Status') }} - {{ __('All') }}</option>
                    <option value="pending">{{ __('Pending') }}</option>
                    <option value="confirmed">{{ __('Confirmed') }}</option>
                    <option value="delivering">{{ __('Delivering') }}</option>
                    <option value="delivered">{{ __('Delivered') }}</option>
                    <option value="cancelled">{{ __('Cancelled') }}</option>
                </select>
            </div>
            <div class="col-md-3">
                <select id="filterSalesman" class="form-select form-select-sm">
                    <option value="">{{ __('Salesman') }} - {{ __('All') }}</option>
                    @foreach($salesmen as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <table id="ordersTable" class="table table-striped table-bordered w-100">
            <thead class="table-dark">
                <tr>
                    <th>رقم الطلب</th>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Salesman') }}</th>
                    <th>{{ __('Order Date') }}</th>
                    <th>{{ __('Net Total') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    const table = $('#ordersTable').DataTable({
        processing: true, serverSide: true, responsive: true,
        order: [[3, 'desc']],
        ajax: {
            url: "{{ route('orders.data') }}",
            data: d => Object.assign(d, {
                from: $('#from').val(),
                to: $('#to').val(),
                status: $('#filterStatus').val(),
                salesman_id: $('#filterSalesman').val()
            })
        },
        columns: [
            { data: 'order_number', name: 'order_number' },
            { data: 'customer_name', name: 'customer.name' },
            { data: 'salesman_name', name: 'salesman.name' },
            { data: 'order_date', name: 'order_date' },
            { data: 'net_total', name: 'net_total', className: 'text-end' },
            { data: 'status_badge', name: 'status', orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        language: window.dtArabic
    });

    $('#from, #to, #filterStatus, #filterSalesman').on('change', () => table.ajax.reload());
});
</script>
@endpush
