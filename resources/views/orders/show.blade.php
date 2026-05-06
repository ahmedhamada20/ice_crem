@extends('layouts.admin')
@section('title', $order->order_number)
@section('page_title', 'طلب ' . $order->order_number)

@section('content')
<div class="row g-3">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between">
                <h6 class="mb-0">{{ $order->order_number }} {!! $order->status_badge !!}</h6>
                <div>
                    @can('confirm', $order)
                    <button class="btn btn-sm btn-success" id="btnConfirm"><i class="bi bi-check-circle"></i> تأكيد</button>
                    @endcan
                    @can('cancel', $order)
                    <button class="btn btn-sm btn-danger" id="btnCancel"><i class="bi bi-x-circle"></i> إلغاء</button>
                    @endcan
                    <a class="btn btn-sm btn-secondary" href="{{ route('orders.print', $order) }}" target="_blank"><i class="bi bi-printer"></i></a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4"><strong>{{ __('Customer') }}:</strong> {{ $order->customer->name }}</div>
                    <div class="col-md-4"><strong>{{ __('Salesman') }}:</strong> {{ $order->salesman?->name ?? '-' }}</div>
                    <div class="col-md-4"><strong>{{ __('Order Date') }}:</strong> {{ $order->order_date->format('d/m/Y') }}</div>
                </div>

                <table class="table table-sm">
                    <thead class="table-light">
                        <tr><th>المنتج</th><th>الكمية</th><th>السعر</th><th>الخصم</th><th>الإجمالي</th></tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->product->name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format((float) $item->price, 2) }}</td>
                            <td>{{ number_format((float) $item->discount, 2) }}</td>
                            <td>{{ number_format((float) $item->total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                @if($order->notes)
                <div class="alert alert-light mt-3"><strong>{{ __('Notes') }}:</strong> {{ $order->notes }}</div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header"><h6 class="mb-0">الإجماليات</h6></div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2"><span>{{ __('Subtotal') }}:</span><strong>{{ number_format((float) $order->subtotal, 2) }}</strong></div>
                <div class="d-flex justify-content-between mb-2"><span>{{ __('Discount') }}:</span><strong>{{ number_format((float) $order->discount, 2) }}</strong></div>
                <div class="d-flex justify-content-between mb-2"><span>{{ __('Tax') }}:</span><strong>{{ number_format((float) $order->tax, 2) }}</strong></div>
                <hr>
                <div class="d-flex justify-content-between fs-5"><span>{{ __('Net Total') }}:</span><strong class="text-primary">{{ number_format((float) $order->net_total, 2) }}</strong></div>
            </div>
        </div>

        <div class="card shadow-sm mt-3">
            <div class="card-header"><h6 class="mb-0">المراحل</h6></div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="bi bi-clock"></i> أُنشئ: {{ $order->created_at->format('d/m/Y H:i') }}</li>
                @if($order->confirmed_at)
                <li class="list-group-item"><i class="bi bi-check-circle text-success"></i> تأكد: {{ $order->confirmed_at->format('d/m/Y H:i') }}</li>
                @endif
                @if($order->delivery)
                <li class="list-group-item"><i class="bi bi-truck"></i> التوصيل: {{ $order->delivery->status }}</li>
                @endif
                @if($order->cancelled_at)
                <li class="list-group-item text-danger"><i class="bi bi-x-circle"></i> إلغاء: {{ $order->cancelled_at->format('d/m/Y H:i') }}</li>
                @endif
            </ul>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#btnConfirm').on('click', function () {
    Swal.fire({ title: 'تأكيد الطلب؟', text: 'سيتم خصم الكمية من المخزون.', icon: 'question', showCancelButton: true })
        .then(r => r.isConfirmed && $.post("{{ route('orders.confirm', $order) }}", { _token: "{{ csrf_token() }}" })
            .done(d => { toastr.success(d.message); setTimeout(() => location.reload(), 800); })
            .fail(x => toastr.error(x.responseJSON?.message || 'خطأ')));
});

$('#btnCancel').on('click', function () {
    Swal.fire({ title: 'سبب الإلغاء؟', input: 'text', showCancelButton: true })
        .then(r => r.isConfirmed && $.post("{{ route('orders.cancel', $order) }}", { _token: "{{ csrf_token() }}", reason: r.value })
            .done(d => { toastr.success(d.message); setTimeout(() => location.reload(), 800); })
            .fail(x => toastr.error(x.responseJSON?.message || 'خطأ')));
});
</script>
@endpush
