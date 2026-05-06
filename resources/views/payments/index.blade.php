@extends('layouts.admin')
@section('title', __('Payments'))
@section('page_title', __('Payments'))

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-cash-coin"></i> {{ __('Payments') }}</h5>
        <button class="btn btn-primary btn-sm" id="btnAdd"><i class="bi bi-plus-lg"></i> {{ __('Add New') }}</button>
    </div>
    <div class="card-body">
        <div class="row g-2 mb-3">
            <div class="col-md-3"><input type="date" id="from" class="form-control form-control-sm"></div>
            <div class="col-md-3"><input type="date" id="to" class="form-control form-control-sm"></div>
        </div>
        <table id="dt" class="table table-striped w-100">
            <thead class="table-dark">
                <tr><th>رقم</th><th>التاريخ</th><th>{{ __('Customer') }}</th><th>{{ __('Invoice') }}</th><th>المبلغ</th><th>الطريقة</th><th>المُحصّل</th></tr>
            </thead>
        </table>
    </div>
</div>

<div class="modal fade" id="formModal">
    <div class="modal-dialog">
        <form id="form" class="modal-content">
            @csrf
            <div class="modal-header"><h6 class="modal-title">دفعة جديدة</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label>{{ __('Customer') }} *</label>
                        <select name="customer_id" id="customer_id" class="form-select" required>
                            <option value="">--</option>
                            @foreach(\App\Models\Customer::active()->get() as $c)<option value="{{ $c->id }}">{{ $c->code }} - {{ $c->name }} (الرصيد: {{ number_format((float) $c->balance, 2) }})</option>@endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label>{{ __('Invoice') }} (اختياري)</label>
                        <select name="invoice_id" id="invoice_id" class="form-select">
                            <option value="">دفعة عامة</option>
                        </select>
                        <small class="text-muted">يتم تحميل الفواتير غير المسددة عند اختيار العميل</small>
                    </div>
                    <div class="col-md-6"><label>التاريخ *</label><input type="date" name="payment_date" id="payment_date" class="form-control" value="{{ now()->toDateString() }}" required></div>
                    <div class="col-md-6"><label>المبلغ *</label><input type="number" step="0.01" min="0.01" name="amount" id="amount" class="form-control" required></div>
                    <div class="col-md-6">
                        <label>الطريقة *</label>
                        <select name="method" id="method" class="form-select" required>
                            <option value="cash">{{ __('cash') }}</option>
                            <option value="bank">{{ __('bank') }}</option>
                            <option value="cheque">{{ __('cheque') }}</option>
                        </select>
                    </div>
                    <div class="col-md-6"><label>المرجع</label><input type="text" name="reference" id="reference" class="form-control" placeholder="رقم الشيك / التحويل"></div>
                    <div class="col-12"><label>{{ __('Notes') }}</label><textarea name="notes" id="notes" class="form-control" rows="2"></textarea></div>
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
    const t = $('#dt').DataTable({
        processing: true, serverSide: true,
        ajax: { url: "{{ route('payments.data') }}", data: d => Object.assign(d, { from: $('#from').val(), to: $('#to').val() }) },
        columns: [
            { data: 'payment_number' }, { data: 'payment_date' }, { data: 'customer_name' },
            { data: 'invoice_number' }, { data: 'amount', className: 'text-end' },
            { data: 'method' }, { data: 'user_name' }
        ],
        language: window.dtArabic
    });

    $('#from, #to').on('change', () => t.ajax.reload());

    $('#btnAdd').on('click', () => { $('#form')[0].reset(); $('#payment_date').val('{{ now()->toDateString() }}'); $('#invoice_id').html('<option value="">دفعة عامة</option>'); $('#formModal').modal('show'); });

    // When customer changes, load their unpaid invoices
    $('#customer_id').on('change', function () {
        const cid = $(this).val();
        $('#invoice_id').html('<option value="">دفعة عامة</option>');
        if (!cid) return;
        $.get('{{ url("invoices/data") }}?length=1000&customer_id=' + cid, function (resp) {
            (resp.data || []).filter(i => i.balance > 0).forEach(i => {
                $('#invoice_id').append(`<option value="${i.id}">${i.invoice_number} (متبقي ${parseFloat(i.balance).toFixed(2)})</option>`);
            });
        });
    });

    $('#form').on('submit', function (e) {
        e.preventDefault();
        $.ajax({ url: "{{ route('payments.store') }}", method: 'POST', data: $(this).serialize(),
            success: r => { $('#formModal').modal('hide'); toastr.success(r.message); t.ajax.reload(); },
            error: x => toastr.error(x.responseJSON?.message || '{{ __("Operation failed") }}')
        });
    });
});
</script>
@endpush
