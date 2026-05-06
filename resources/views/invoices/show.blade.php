@extends('layouts.admin')
@section('title', $invoice->invoice_number)
@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between">
        <h6>{{ $invoice->invoice_number }} {!! $invoice->status_badge !!}</h6>
        <div>
            <a class="btn btn-sm btn-secondary" href="{{ route('invoices.print', $invoice) }}" target="_blank"><i class="bi bi-printer"></i> طباعة</a>
            @can('markPaid', $invoice)
            @if($invoice->status !== 'paid')
            <button class="btn btn-sm btn-success" id="btnMarkPaid">دفع كامل</button>
            @endif
            @endcan
        </div>
    </div>
    <div class="card-body">
        <p><strong>{{ __('Customer') }}:</strong> {{ $invoice->customer->name }}</p>
        <p><strong>تاريخ الإصدار:</strong> {{ $invoice->issue_date->format('d/m/Y') }} | <strong>الاستحقاق:</strong> {{ $invoice->due_date?->format('d/m/Y') }}</p>
        <table class="table">
            <tr><td>{{ __('Total') }}</td><td>{{ number_format((float) $invoice->total, 2) }}</td></tr>
            <tr><td>مدفوع</td><td>{{ number_format((float) $invoice->paid, 2) }}</td></tr>
            <tr><td>{{ __('Balance') }}</td><td><strong>{{ number_format((float) $invoice->balance, 2) }}</strong></td></tr>
        </table>

        <h6>المدفوعات</h6>
        <table class="table table-sm">
            <thead><tr><th>التاريخ</th><th>المبلغ</th><th>الطريقة</th><th>المرجع</th></tr></thead>
            <tbody>
            @forelse($invoice->payments as $p)
                <tr><td>{{ $p->payment_date->format('d/m/Y') }}</td><td>{{ number_format((float) $p->amount, 2) }}</td><td>{{ $p->method_label }}</td><td>{{ $p->reference }}</td></tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted">لا توجد مدفوعات</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#btnMarkPaid').on('click', () => {
    Swal.fire({ title: 'تأكيد دفع كامل؟', icon: 'question', showCancelButton: true })
        .then(r => r.isConfirmed && $.post("{{ route('invoices.markpaid', $invoice) }}", { _token: "{{ csrf_token() }}" })
            .done(d => { toastr.success(d.message); setTimeout(() => location.reload(), 800); }));
});
</script>
@endpush
