@extends('layouts.driver')
@section('title', 'توصيلاتي')

@php
    $assigned    = $deliveries->where('status', 'assigned')->count();
    $inProgress  = $deliveries->where('status', 'in_progress')->count();
    $totalToday  = $deliveries->where('status', 'delivered')->where('delivered_at', '>=', now()->startOfDay())->count();
@endphp

@section('content')
    {{-- Stats --}}
    <div class="stats-row">
        <div class="stat-card warn">
            <div class="num">{{ $assigned }}</div>
            <div class="lbl">معلّقة</div>
        </div>
        <div class="stat-card">
            <div class="num">{{ $inProgress }}</div>
            <div class="lbl">جاري التنفيذ</div>
        </div>
        <div class="stat-card success">
            <div class="num">{{ $totalToday }}</div>
            <div class="lbl">تمت اليوم</div>
        </div>
    </div>

    {{-- Refresh button --}}
    <div class="d-flex justify-content-between align-items-center mt-3 mb-2 px-1">
        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-list-check"></i> توصيلاتي الحالية</h6>
        <button class="btn btn-sm btn-light" onclick="location.reload()" title="تحديث">
            <i class="bi bi-arrow-clockwise"></i>
        </button>
    </div>

    {{-- Deliveries list --}}
    @forelse($deliveries as $d)
        <div class="delivery-card {{ str_replace('_', '-', $d->status) }}">
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <div class="order-num">طلب: {{ $d->order->order_number }}</div>
                    <div class="customer">{{ $d->order->customer->name }}</div>
                    <div class="meta mb-1">
                        <i class="bi bi-telephone"></i>
                        <a href="tel:{{ $d->order->customer->phone }}" class="text-decoration-none text-secondary">
                            {{ $d->order->customer->phone }}
                        </a>
                    </div>
                    <div class="meta mb-2">
                        <i class="bi bi-geo-alt"></i> {{ Str::limit($d->order->customer->address ?? '-', 60) }}
                    </div>
                </div>
                <span class="pill {{ str_replace('_', '-', $d->status) }}">
                    @switch($d->status)
                        @case('assigned')    معيّن @break
                        @case('in_progress') قيد التنفيذ @break
                        @case('delivered')   تم التسليم @break
                        @default {{ $d->status }}
                    @endswitch
                </span>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
                <span class="total">{{ number_format((float) $d->order->net_total, 2) }} ج.م</span>
                @if($d->order->customer->location_lat && $d->order->customer->location_lng)
                <a href="https://www.google.com/maps/dir/?api=1&destination={{ $d->order->customer->location_lat }},{{ $d->order->customer->location_lng }}"
                   target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-geo-alt-fill"></i> الاتجاهات
                </a>
                @endif
            </div>

            @if($d->status === 'assigned')
                <button class="btn-action btn-start" data-id="{{ $d->id }}">
                    <i class="bi bi-play-fill"></i> ابدأ التوصيل
                </button>
            @elseif($d->status === 'in_progress')
                <div class="d-grid gap-2">
                    <button class="btn-action success btn-complete" data-id="{{ $d->id }}">
                        <i class="bi bi-check-circle-fill"></i> اكمل التسليم
                    </button>
                    <button class="btn-action outline btn-fail" data-id="{{ $d->id }}">
                        <i class="bi bi-x-circle"></i> فشل التسليم
                    </button>
                </div>
            @endif
        </div>
    @empty
        <div class="empty">
            <i class="bi bi-inbox"></i>
            <h6 class="mt-3">لا توجد توصيلات حالية</h6>
            <p class="small mb-0">سيتم إعلامك عند تعيين توصيلة جديدة لك</p>
        </div>
    @endforelse

    {{-- Complete-delivery modal --}}
    <div class="modal fade" id="completeModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title"><i class="bi bi-check-circle text-success"></i> تأكيد التسليم</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="completeId">
                    <p class="small text-muted mb-2">يرجى توقيع العميل بإصبعك أو بالماوس:</p>
                    <canvas id="sigPad" style="width: 100%; height: 160px;"></canvas>
                    <button type="button" class="btn btn-sm btn-link w-100 mt-1" id="clearSig">
                        <i class="bi bi-eraser"></i> مسح
                    </button>
                    <div class="mt-2">
                        <label class="small fw-bold">ملاحظات (اختياري)</label>
                        <textarea id="completeNotes" class="form-control" rows="2" placeholder="مثلاً: استلم بنفسه..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button class="btn-action success px-3" id="btnSubmitComplete" style="width: auto;">
                        <i class="bi bi-check"></i> تأكيد
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
let pad;

// ── Start delivery ─────────────────────────────────
$('.btn-start').on('click', function () {
    const $btn = $(this);
    const id = $btn.data('id');
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> جارٍ تحديد الموقع...');

    if (!navigator.geolocation) { return doStart(id, null, null, $btn); }

    navigator.geolocation.getCurrentPosition(
        pos => doStart(id, pos.coords.latitude, pos.coords.longitude, $btn),
        () => doStart(id, null, null, $btn),
        { timeout: 8000 }
    );
});

function doStart(id, lat, lng, $btn) {
    $.post("{{ url('driver-app/deliveries') }}/" + id + "/start", { _token: "{{ csrf_token() }}", lat, lng })
        .done(() => { toastr.success('تم بدء التوصيل'); setTimeout(() => location.reload(), 600); })
        .fail(() => { toastr.error('خطأ في بدء التوصيل'); $btn.prop('disabled', false).html('<i class="bi bi-play-fill"></i> ابدأ التوصيل'); });
}

// ── Complete delivery (open modal) ─────────────────
$('.btn-complete').on('click', function () {
    $('#completeId').val($(this).data('id'));
    $('#completeNotes').val('');
    $('#completeModal').modal('show');

    setTimeout(() => {
        const c = document.getElementById('sigPad');
        c.width = c.offsetWidth;
        c.height = 160;
        if (pad) pad.clear(); else pad = new SignaturePad(c, { minWidth: 1, maxWidth: 2.5 });
    }, 250);
});

$('#clearSig').on('click', () => pad?.clear());

$('#btnSubmitComplete').on('click', function () {
    const $btn = $(this);
    const id = $('#completeId').val();
    const sig = (pad && !pad.isEmpty()) ? pad.toDataURL('image/png') : null;

    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

    const submit = (lat, lng) => {
        $.post("{{ url('driver-app/deliveries') }}/" + id + "/complete", {
            _token: "{{ csrf_token() }}", lat, lng, signature: sig, notes: $('#completeNotes').val()
        })
        .done(() => { toastr.success('تم التسليم بنجاح'); $('#completeModal').modal('hide'); setTimeout(() => location.reload(), 700); })
        .fail(x => { toastr.error(x.responseJSON?.message || 'خطأ في التسليم'); $btn.prop('disabled', false).html('<i class="bi bi-check"></i> تأكيد'); });
    };

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            pos => submit(pos.coords.latitude, pos.coords.longitude),
            () => submit(null, null),
            { timeout: 8000 }
        );
    } else { submit(null, null); }
});

// ── Fail delivery ─────────────────────────────────
$('.btn-fail').on('click', function () {
    const id = $(this).data('id');
    Swal.fire({
        title: 'سبب فشل التسليم؟',
        input: 'select',
        inputOptions: {
            'العميل غير متواجد': 'العميل غير متواجد',
            'المحل مغلق': 'المحل مغلق',
            'العميل رفض الاستلام': 'العميل رفض الاستلام',
            'مشكلة في المنتج': 'مشكلة في المنتج',
            'أخرى': 'أخرى'
        },
        inputPlaceholder: 'اختر السبب',
        showCancelButton: true,
        confirmButtonText: 'إرسال', cancelButtonText: 'إلغاء',
        inputValidator: v => !v && 'لازم تختار سبب'
    }).then(r => {
        if (!r.isConfirmed) return;
        $.post("{{ url('driver-app/deliveries') }}/" + id + "/fail", { _token: "{{ csrf_token() }}", reason: r.value })
            .done(() => { toastr.success('تم تسجيل فشل التسليم'); setTimeout(() => location.reload(), 600); })
            .fail(() => toastr.error('خطأ'));
    });
});

// ── Bottom-nav: history (placeholder) ──────────────
$('#navHistory').on('click', e => { e.preventDefault(); toastr.info('السجل قريباً'); });

// ── Periodic location ping (optional) ──────────────
@if($inProgress > 0)
if (navigator.geolocation) {
    setInterval(() => {
        navigator.geolocation.getCurrentPosition(pos => {
            $.post("{{ url('driver-app/location') }}", {
                _token: "{{ csrf_token() }}",
                lat: pos.coords.latitude,
                lng: pos.coords.longitude
            });
        });
    }, 60000); // every 60 seconds
}
@endif
</script>
@endpush
