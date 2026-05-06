@extends('layouts.admin')
@section('title', __('Invoices'))
@section('page_title', __('Invoices'))

@section('content')

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-stat kpi-primary">
            <div class="kpi-icon"><i class="bi bi-receipt"></i></div>
            <div class="kpi-info"><small>إجمالي الفواتير</small><h3>{{ $stats['total'] }}</h3></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-stat kpi-warning">
            <div class="kpi-icon"><i class="bi bi-clock-history"></i></div>
            <div class="kpi-info"><small>غير مدفوعة</small><h3>{{ $stats['unpaid'] + $stats['partial'] }}</h3></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-stat kpi-danger">
            <div class="kpi-icon"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="kpi-info"><small>متأخرات</small><h3>{{ number_format($stats['overdue_amt'], 0) }}</h3></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-stat kpi-success">
            <div class="kpi-icon"><i class="bi bi-cash-coin"></i></div>
            <div class="kpi-info"><small>إجمالي المستحقات</small><h3>{{ number_format($stats['outstanding'], 0) }}</h3></div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
        <div>
            <h5 class="mb-0 fw-bold"><i class="bi bi-receipt text-primary"></i> {{ __('Invoices') }}</h5>
            <small class="text-muted">إدارة الفواتير والمستحقات</small>
        </div>
        @can('create', App\Models\Invoice::class)
        <a href="{{ route('invoices.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> فاتورة جديدة
        </a>
        @endcan
    </div>
    <div class="card-body">

        <div class="d-flex flex-wrap gap-2 mb-3 filter-pills">
            <button type="button" class="filter-pill active" data-status=""><i class="bi bi-grid-fill"></i> الكل <span class="count-badge">{{ $stats['total'] }}</span></button>
            <button type="button" class="filter-pill pill-warning" data-status="unpaid"><i class="bi bi-clock"></i> غير مدفوعة @if($stats['unpaid'] > 0)<span class="count-badge">{{ $stats['unpaid'] }}</span>@endif</button>
            <button type="button" class="filter-pill pill-info" data-status="partial"><i class="bi bi-circle-half"></i> جزئية @if($stats['partial'] > 0)<span class="count-badge">{{ $stats['partial'] }}</span>@endif</button>
            <button type="button" class="filter-pill pill-success" data-status="paid"><i class="bi bi-check-circle"></i> مدفوعة @if($stats['paid'] > 0)<span class="count-badge">{{ $stats['paid'] }}</span>@endif</button>
            <button type="button" class="filter-pill pill-danger" data-status="overdue"><i class="bi bi-exclamation-triangle"></i> متأخرة @if($stats['overdue'] > 0)<span class="count-badge">{{ $stats['overdue'] }}</span>@endif</button>
            <button type="button" class="filter-pill pill-dark" data-status="cancelled"><i class="bi bi-x-circle"></i> ملغاة</button>
        </div>

        <div class="filters-bar mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-4"><label class="form-label small mb-1"><i class="bi bi-calendar"></i> {{ __('From Date') }}</label><input type="date" id="from" class="form-control form-control-sm"></div>
                <div class="col-md-4"><label class="form-label small mb-1"><i class="bi bi-calendar"></i> {{ __('To Date') }}</label><input type="date" id="to" class="form-control form-control-sm"></div>
                <div class="col-md-4"><button class="btn btn-sm btn-outline-secondary w-100" id="btnReset"><i class="bi bi-arrow-clockwise"></i> إعادة تعيين</button></div>
            </div>
        </div>

        <div class="table-responsive">
            <table id="dt" class="table pretty-table table-hover w-100 align-middle">
                <thead><tr>
                    <th>رقم</th><th>{{ __('Customer') }}</th><th>الإصدار</th><th>الاستحقاق</th>
                    <th class="text-end">{{ __('Total') }}</th><th class="text-end">مدفوع</th>
                    <th class="text-end">{{ __('Balance') }}</th><th>{{ __('Status') }}</th>
                    <th class="text-center">{{ __('Actions') }}</th>
                </tr></thead>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    let currentStatus = '';
    const t = $('#dt').DataTable({
        processing: true, serverSide: true, responsive: true, order: [[2, 'desc']], pageLength: 25,
        ajax: { url: "{{ route('invoices.data') }}", data: function (d) {
            d.from = $('#from').val(); d.to = $('#to').val(); d.status = currentStatus;
        }},
        columns: [
            { data: 'invoice_number', name: 'invoice_number' },
            { data: 'customer_name',  name: 'customer.name' },
            { data: 'issue_date',     name: 'issue_date' },
            { data: 'due_date',       name: 'due_date' },
            { data: 'total',          name: 'total', className: 'text-end' },
            { data: 'paid',           name: 'paid',  className: 'text-end' },
            { data: 'balance',        name: 'balance', className: 'text-end' },
            { data: 'status_badge',   name: 'status', orderable: false },
            { data: 'actions',        name: 'actions', orderable: false, searchable: false, className: 'text-center' }
        ],
        dom: 'Bfrtip',
        buttons: [
            { extend: 'excel', text: '<i class="bi bi-file-earmark-excel"></i> Excel', className: 'btn btn-sm btn-success' },
            { extend: 'print', text: '<i class="bi bi-printer"></i> طباعة',           className: 'btn btn-sm btn-info' },
        ]
    });

    $('.filter-pill').on('click', function () {
        $('.filter-pill').removeClass('active');
        $(this).addClass('active');
        currentStatus = $(this).data('status') || '';
        t.ajax.reload();
    });

    $('#from, #to').on('change', () => t.ajax.reload());
    $('#btnReset').on('click', () => {
        $('#from, #to').val('');
        $('.filter-pill').removeClass('active');
        $('.filter-pill[data-status=""]').addClass('active');
        currentStatus = '';
        t.ajax.reload();
    });
});
</script>
@endpush
