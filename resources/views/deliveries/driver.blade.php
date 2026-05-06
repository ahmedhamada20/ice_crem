@extends('layouts.admin')
@section('title', 'لوحة السائق')
@section('page_title', 'توصيلاتي')

@section('content')
<div class="row g-3">
    @forelse($deliveries as $d)
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6>{{ $d->order->order_number }}</h6>
                <p class="mb-1"><i class="bi bi-shop"></i> {{ $d->order->customer->name }}</p>
                <p class="mb-1"><i class="bi bi-telephone"></i> {{ $d->order->customer->phone }}</p>
                <p class="mb-1 small text-muted">{{ $d->order->customer->address }}</p>
                <p>الإجمالي: <strong>{{ number_format((float) $d->order->net_total, 2) }}</strong></p>

                @if($d->status === 'assigned')
                    <button class="btn btn-success btn-sm w-100 btn-start" data-id="{{ $d->id }}"><i class="bi bi-play"></i> ابدأ التوصيل</button>
                @elseif($d->status === 'in_progress')
                    <button class="btn btn-primary btn-sm w-100 btn-complete" data-id="{{ $d->id }}"><i class="bi bi-check-circle"></i> اكمل التوصيل</button>
                @endif
            </div>
        </div>
    </div>
    @empty
        <div class="col-12 text-center text-muted">لا توجد توصيلات حالية</div>
    @endforelse
</div>

<div class="modal fade" id="completeModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h6>إكمال التسليم</h6></div>
            <div class="modal-body">
                <input type="hidden" id="completeId">
                <p class="small">يرجى توقيع العميل بالأسفل:</p>
                <canvas id="sigPad" style="border: 1px solid #ccc; width: 100%; height: 150px; touch-action: none;"></canvas>
                <button type="button" class="btn btn-link btn-sm" id="clearSig">مسح</button>
                <textarea id="completeNotes" class="form-control" rows="2" placeholder="{{ __('Notes') }}"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-success" id="btnSubmitComplete">{{ __('Confirm') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
let pad;

$('.btn-start').on('click', function () {
    const id = $(this).data('id');
    navigator.geolocation.getCurrentPosition(pos => {
        $.post("{{ url('api/driver/deliveries') }}/" + id + "/start", {
            _token: "{{ csrf_token() }}",
            lat: pos.coords.latitude,
            lng: pos.coords.longitude
        }).done(() => location.reload());
    });
});

$('.btn-complete').on('click', function () {
    $('#completeId').val($(this).data('id'));
    $('#completeModal').modal('show');
    setTimeout(() => {
        const c = document.getElementById('sigPad');
        c.width = c.offsetWidth; c.height = 150;
        pad = new SignaturePad(c);
    }, 250);
});

$('#clearSig').on('click', () => pad?.clear());

$('#btnSubmitComplete').on('click', function () {
    const id = $('#completeId').val();
    const sig = pad?.isEmpty() ? null : pad.toDataURL('image/png');

    navigator.geolocation.getCurrentPosition(pos => {
        $.post("{{ url('api/driver/deliveries') }}/" + id + "/complete", {
            _token: "{{ csrf_token() }}",
            lat: pos.coords.latitude,
            lng: pos.coords.longitude,
            signature: sig,
            notes: $('#completeNotes').val()
        }).done(() => { toastr.success('تم التسليم'); setTimeout(() => location.reload(), 800); })
          .fail(x => toastr.error(x.responseJSON?.message || 'خطأ'));
    });
});
</script>
@endpush
