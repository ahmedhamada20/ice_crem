@extends('layouts.admin')
@section('title', __('Invoices'))
@section('page_title', __('Invoices'))

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-receipt"></i> {{ __('Invoices') }}</h5>
        @can('create', App\Models\Invoice::class)
        <a href="{{ route('invoices.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> فاتورة جديدة
        </a>
        @endcan
    </div>
    <div class="card-body">
        <div class="row g-2 mb-3">
            <div class="col-md-2"><input type="date" id="from" class="form-control form-control-sm"></div>
            <div class="col-md-2"><input type="date" id="to" class="form-control form-control-sm"></div>
            <div class="col-md-3">
                <select id="filterStatus" class="form-select form-select-sm">
                    <option value="">{{ __('Status') }} - {{ __('All') }}</option>
                    <option value="unpaid">{{ __('Unpaid') }}</option>
                    <option value="partial">{{ __('Partial') }}</option>
                    <option value="paid">{{ __('Paid') }}</option>
                    <option value="overdue">{{ __('Overdue') }}</option>
                </select>
            </div>
        </div>
        <table id="dt" class="table table-striped w-100">
            <thead class="table-dark">
                <tr><th>رقم</th><th>{{ __('Customer') }}</th><th>الإصدار</th><th>الاستحقاق</th><th>{{ __('Total') }}</th><th>مدفوع</th><th>{{ __('Balance') }}</th><th>{{ __('Status') }}</th><th>{{ __('Actions') }}</th></tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
const t = $('#dt').DataTable({ processing: true, serverSide: true,
    ajax: { url: "{{ route('invoices.data') }}", data: d => Object.assign(d, { from: $('#from').val(), to: $('#to').val(), status: $('#filterStatus').val() }) },
    columns: [{ data: 'invoice_number' }, { data: 'customer_name' }, { data: 'issue_date' }, { data: 'due_date' },
              { data: 'total', className: 'text-end' }, { data: 'paid', className: 'text-end' }, { data: 'balance', className: 'text-end' },
              { data: 'status_badge' }, { data: 'actions', orderable: false, searchable: false }],
    language: window.dtArabic });

$('#from, #to, #filterStatus').on('change', () => t.ajax.reload());
</script>
@endpush
