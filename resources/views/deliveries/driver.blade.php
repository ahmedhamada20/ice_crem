@extends('layouts.driver')
@section('title', 'توصيلاتي')

@section('content')
    {{-- Stats (real numbers from controller) --}}
    <div class="stats-row">
        <div class="stat-card warn">
            <div class="num">{{ $stats['assigned'] + $stats['in_progress'] }}</div>
            <div class="lbl">توصيلات اليوم</div>
        </div>
        <div class="stat-card">
            <div class="num">{{ $stats['upcoming'] }}</div>
            <div class="lbl">القادمة</div>
        </div>
        <div class="stat-card success">
            <div class="num">{{ $stats['today_done'] }}</div>
            <div class="lbl">تمت اليوم</div>
        </div>
    </div>

    {{-- Today's revenue summary --}}
    @if($stats['today_done'] > 0)
    <div class="today-revenue mt-2 mb-1">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-cash-coin"></i>
                <small>إجمالي توصيلات اليوم</small>
            </div>
            <strong>{{ number_format($stats['today_revenue'], 2) }} ج.م</strong>
        </div>
    </div>
    @endif

    {{-- ─────────────────────────────────────────────────
         Section 1: TODAY's deliveries (actionable)
         ───────────────────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center mt-3 mb-2 px-1">
        <h6 class="mb-0 fw-bold text-dark">
            <i class="bi bi-list-check"></i> توصيلات اليوم
            <span class="badge bg-primary">{{ $todayDeliveries->count() }}</span>
        </h6>
        <button class="btn btn-sm btn-light" onclick="location.reload()" title="تحديث">
            <i class="bi bi-arrow-clockwise"></i>
        </button>
    </div>

    @forelse($todayDeliveries as $d)
        @php
            $isOverdue = $d->order->delivery_date && $d->order->delivery_date->lt(now()->startOfDay());
        @endphp
        <div class="delivery-card {{ str_replace('_', '-', $d->status) }}">

            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="pill {{ str_replace('_', '-', $d->status) }}">
                    @switch($d->status)
                        @case('assigned')    <i class="bi bi-clock"></i> معيّن @break
                        @case('in_progress') <i class="bi bi-truck"></i> قيد التنفيذ @break
                        @default {{ $d->status }}
                    @endswitch
                </span>
                <small class="text-muted" style="font-size: .72rem;">{{ $d->order->order_number }}</small>
            </div>

            @if($isOverdue)
                <div class="mb-1">
                    <span class="badge bg-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        متأخر — كان موعدها {{ $d->order->delivery_date->format('d/m/Y') }}
                    </span>
                </div>
            @elseif($d->order->delivery_date)
                <div class="mb-1">
                    <small class="text-muted"><i class="bi bi-calendar-check"></i> موعد التسليم: اليوم</small>
                </div>
            @endif

            <div class="customer mb-1">{{ $d->order->customer->name }}</div>
            <div class="meta mb-1">
                <i class="bi bi-telephone"></i>
                <a href="tel:{{ $d->order->customer->phone }}" class="text-decoration-none text-secondary">
                    {{ $d->order->customer->phone }}
                </a>
            </div>
            <div class="meta mb-2">
                <i class="bi bi-geo-alt"></i> {{ Str::limit($d->order->customer->address ?? '-', 60) }}
            </div>

            <div class="d-flex justify-content-between align-items-center my-2 pt-2 border-top">
                <span class="total">{{ number_format((float) $d->order->net_total, 2) }} ج.م</span>
                @if($d->order->customer->location_lat && $d->order->customer->location_lng)
                <a href="https://www.google.com/maps/dir/?api=1&destination={{ $d->order->customer->location_lat }},{{ $d->order->customer->location_lng }}"
                   target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-geo-alt-fill"></i> الاتجاهات
                </a>
                @endif
            </div>

            @if($d->status === 'assigned')
                <button class="btn-action btn-start mt-2" data-id="{{ $d->id }}">
                    <i class="bi bi-play-fill"></i> ابدأ التوصيل
                </button>
            @elseif($d->status === 'in_progress')
                <div class="d-grid gap-2 mt-2">
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
            <i class="bi bi-check2-circle"></i>
            <h6 class="mt-3">لا توجد توصيلات لليوم</h6>
            <p class="small mb-0">يومك خفيف! 🎉</p>
        </div>
    @endforelse

    {{-- ─────────────────────────────────────────────────
         Section 2: UPCOMING deliveries (read-only)
         ───────────────────────────────────────────────── --}}
    @if($upcomingDeliveries->count() > 0)
        <div class="d-flex justify-content-between align-items-center mt-4 mb-2 px-1">
            <h6 class="mb-0 fw-bold text-dark">
                <i class="bi bi-calendar3"></i> التوصيلات القادمة
                <span class="badge bg-secondary">{{ $upcomingDeliveries->count() }}</span>
            </h6>
            <small class="text-muted">للعرض فقط</small>
        </div>

        @php $currentDate = null; @endphp
        @foreach($upcomingDeliveries as $d)
            @php
                $deliveryDate = $d->order->delivery_date;
                $dateLabel = $deliveryDate?->format('Y-m-d');
                $diffDays  = $deliveryDate?->diffInDays(now()->startOfDay());
            @endphp

            @if($dateLabel !== $currentDate)
                @php $currentDate = $dateLabel; @endphp
                <div class="upcoming-date-header">
                    <i class="bi bi-calendar-event"></i>
                    {{ $deliveryDate?->translatedFormat('l - d/m/Y') }}
                    <span class="text-muted small">
                        ({{ $diffDays === 1 ? 'بكرة' : "بعد {$diffDays} يوم" }})
                    </span>
                </div>
            @endif

            <div class="delivery-card upcoming">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="pill" style="background:#e0e7ff;color:#3730a3;">
                        <i class="bi bi-lock"></i> قادم
                    </span>
                    <small class="text-muted" style="font-size: .72rem;">{{ $d->order->order_number }}</small>
                </div>
                <div class="customer mb-1">{{ $d->order->customer->name }}</div>
                <div class="meta mb-1"><i class="bi bi-telephone"></i> {{ $d->order->customer->phone }}</div>
                <div class="meta mb-2"><i class="bi bi-geo-alt"></i> {{ Str::limit($d->order->customer->address ?? '-', 60) }}</div>
                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                    <span class="total">{{ number_format((float) $d->order->net_total, 2) }} ج.م</span>
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i> الأزرار ستفعل في موعدها
                    </small>
                </div>
            </div>
        @endforeach
    @endif

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

@push('styles')
<style>
.today-revenue {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    border-radius: 14px;
    padding: .75rem 1rem;
    box-shadow: 0 2px 8px rgba(16,185,129,.25);
}
.today-revenue small { opacity: .9; font-size: .8rem; margin-right: .35rem; }
.today-revenue strong { font-size: 1.1rem; }
.delivery-card .border-top { border-color: #f3f4f6 !important; }

/* Upcoming date header */
.upcoming-date-header {
    background: white;
    padding: .55rem .9rem;
    border-radius: 10px;
    margin: .5rem 0;
    font-weight: 700;
    color: #1f2937;
    border-right: 4px solid #6366f1;
    box-shadow: 0 1px 3px rgba(0,0,0,.04);
    font-size: .9rem;
}

/* Upcoming cards: dimmed, no actions */
.delivery-card.upcoming {
    background: #fafbfc;
    opacity: .85;
    border-right-color: #818cf8 !important;
}
.delivery-card.upcoming .customer { color: #4b5563; }
</style>
@endpush

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
        .fail(x => { toastr.error(x.responseJSON?.message || 'خطأ في بدء التوصيل'); $btn.prop('disabled', false).html('<i class="bi bi-play-fill"></i> ابدأ التوصيل'); });
}

// ── Complete delivery ──────────────────────────────
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

// ── Fail delivery ──────────────────────────────────
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
            .fail(x => toastr.error(x.responseJSON?.message || 'خطأ'));
    });
});

// ── Periodic location ping (only if active deliveries exist) ──────────────
@if($stats['in_progress'] > 0)
if (navigator.geolocation) {
    setInterval(() => {
        navigator.geolocation.getCurrentPosition(pos => {
            $.post("{{ url('driver-app/location') }}", {
                _token: "{{ csrf_token() }}",
                lat: pos.coords.latitude,
                lng: pos.coords.longitude
            });
        });
    }, 60000);
}
@endif
</script>
@endpush
