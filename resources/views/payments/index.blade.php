@extends('layouts.admin')
@section('title', __('Payments'))
@section('page_title', __('Payments'))

@section('content')

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-stat kpi-success">
            <div class="kpi-icon"><i class="bi bi-cash-coin"></i></div>
            <div class="kpi-info"><small>تحصيل اليوم</small><h3>{{ number_format($stats['today_total'], 0) }}</h3></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-stat kpi-primary">
            <div class="kpi-icon"><i class="bi bi-wallet2"></i></div>
            <div class="kpi-info"><small>تحصيل الشهر</small><h3>{{ number_format($stats['month_total'], 0) }}</h3></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-stat kpi-info">
            <div class="kpi-icon"><i class="bi bi-receipt"></i></div>
            <div class="kpi-info"><small>عمليات اليوم</small><h3>{{ $stats['today_count'] }}</h3></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-stat kpi-warning">
            <div class="kpi-icon"><i class="bi bi-graph-up"></i></div>
            <div class="kpi-info"><small>عمليات الشهر</small><h3>{{ $stats['month_count'] }}</h3></div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
        <div>
            <h5 class="mb-0 fw-bold"><i class="bi bi-cash-coin text-primary"></i> {{ __('Payments') }}</h5>
            <small class="text-muted">سجل المدفوعات والتحصيلات</small>
        </div>
    </div>
    <div class="card-body">

        <div class="d-flex flex-wrap gap-2 mb-3 filter-pills">
            <button type="button" class="filter-pill active" data-method=""><i class="bi bi-grid-fill"></i> الكل</button>
            <button type="button" class="filter-pill pill-success" data-method="cash"><i class="bi bi-cash"></i> نقدي @if($stats['cash'] > 0)<span class="count-badge">{{ $stats['cash'] }}</span>@endif</button>
            <button type="button" class="filter-pill pill-primary" data-method="bank"><i class="bi bi-bank"></i> بنكي @if($stats['bank'] > 0)<span class="count-badge">{{ $stats['bank'] }}</span>@endif</button>
            <button type="button" class="filter-pill pill-info" data-method="cheque"><i class="bi bi-card-text"></i> شيك @if($stats['cheque'] > 0)<span class="count-badge">{{ $stats['cheque'] }}</span>@endif</button>
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
                    <th>رقم</th><th>التاريخ</th><th>{{ __('Customer') }}</th><th>{{ __('Invoice') }}</th>
                    <th class="text-end">المبلغ</th><th>الطريقة</th><th>المُحصّل</th>
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
    let currentMethod = '';
    const t = $('#dt').DataTable({
        processing: true, serverSide: true, responsive: true, order: [[1, 'desc']], pageLength: 25,
        ajax: { url: "{{ route('payments.data') }}", data: function (d) {
            d.from = $('#from').val(); d.to = $('#to').val(); d.method = currentMethod;
        }},
        columns: [
            { data: 'payment_number', name: 'payment_number' },
            { data: 'payment_date',   name: 'payment_date' },
            { data: 'customer_name',  name: 'customer.name' },
            { data: 'invoice_number', name: 'invoice.invoice_number' },
            { data: 'amount',         name: 'amount', className: 'text-end' },
            { data: 'method',         name: 'method' },
            { data: 'user_name',      name: 'user.name' },
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
        currentMethod = $(this).data('method') || '';
        t.ajax.reload();
    });

    $('#from, #to').on('change', () => t.ajax.reload());
    $('#btnReset').on('click', () => {
        $('#from, #to').val('');
        $('.filter-pill').removeClass('active');
        $('.filter-pill[data-method=""]').addClass('active');
        currentMethod = '';
        t.ajax.reload();
    });

    $('#dt').on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        Swal.fire({ title: '{{ __("Are you sure?") }}', icon: 'warning', showCancelButton: true })
            .then(r => r.isConfirmed && $.ajax({ url: "{{ url('payments') }}/" + id, type: 'DELETE',
                success: () => { toastr.success('{{ __("Deleted successfully") }}'); t.ajax.reload(); }}));
    });
});
</script>
@endpush
