@extends(($isDriverOnly ?? false) ? 'layouts.driver' : 'layouts.admin')
@section('title', $delivery->delivery_number)
@section('page_title', 'تفاصيل التوصيلة')

@php
    $statusMap = [
        'assigned'    => ['label' => 'معيّن',         'cls' => 'warning text-dark', 'icon' => 'clock-fill'],
        'in_progress' => ['label' => 'قيد التنفيذ',   'cls' => 'primary',           'icon' => 'truck'],
        'delivered'   => ['label' => 'تم التسليم',    'cls' => 'success',           'icon' => 'check-circle-fill'],
        'failed'      => ['label' => 'فشل التسليم',   'cls' => 'danger',            'icon' => 'x-circle-fill'],
        'returned'    => ['label' => 'مرتجع',         'cls' => 'dark',              'icon' => 'arrow-counterclockwise'],
    ];
    $st = $statusMap[$delivery->status] ?? ['label' => $delivery->status, 'cls' => 'secondary', 'icon' => 'circle'];
@endphp

@section('content')

@if($isDriverOnly ?? false)
    {{-- ═══════════════════════════════════════════════════════════
         Driver mobile-style detail page
         ═══════════════════════════════════════════════════════════ --}}

    {{-- Back link + status --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ url()->previous() }}" class="btn btn-sm btn-light">
            <i class="bi bi-arrow-right"></i> رجوع
        </a>
        <span class="pill" style="background: {{ in_array($delivery->status, ['delivered']) ? '#d1fae5' : (in_array($delivery->status, ['failed','returned']) ? '#fee2e2' : '#fef3c7') }};
                                  color: {{ in_array($delivery->status, ['delivered']) ? '#065f46' : (in_array($delivery->status, ['failed','returned']) ? '#991b1b' : '#92400e') }};">
            <i class="bi bi-{{ $st['icon'] }}"></i> {{ $st['label'] }}
        </span>
    </div>

    {{-- Order header card --}}
    <div class="delivery-card" style="border-right-width: 5px;">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <div class="order-num">رقم التوصيلة</div>
                <h6 class="mb-0 fw-bold">{{ $delivery->delivery_number }}</h6>
            </div>
            <div class="text-end">
                <div class="order-num">رقم الطلب</div>
                <h6 class="mb-0 fw-bold">{{ $delivery->order->order_number }}</h6>
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
            <span class="meta"><i class="bi bi-calendar"></i> {{ $delivery->order->order_date?->format('d/m/Y') }}</span>
            <span class="total">{{ number_format((float) $delivery->order->net_total, 2) }} ج.م</span>
        </div>
    </div>

    {{-- Customer card --}}
    <div class="delivery-card mt-2">
        <h6 class="fw-bold mb-2"><i class="bi bi-shop text-primary"></i> العميل</h6>
        <div class="customer mb-1">{{ $delivery->order->customer->name }}</div>
        <div class="meta mb-1">
            <i class="bi bi-telephone"></i>
            <a href="tel:{{ $delivery->order->customer->phone }}" class="text-decoration-none">
                {{ $delivery->order->customer->phone }}
            </a>
        </div>
        <div class="meta mb-2">
            <i class="bi bi-geo-alt"></i> {{ $delivery->order->customer->address ?? '-' }}
        </div>
        @if($delivery->order->customer->location_lat && $delivery->order->customer->location_lng)
            <a href="https://www.google.com/maps/dir/?api=1&destination={{ $delivery->order->customer->location_lat }},{{ $delivery->order->customer->location_lng }}"
               target="_blank" class="btn-action outline">
                <i class="bi bi-geo-alt-fill"></i> اتجاهات Google Maps
            </a>
        @endif
    </div>

    {{-- Items card --}}
    <div class="delivery-card mt-2">
        <h6 class="fw-bold mb-2"><i class="bi bi-box-seam text-primary"></i> المنتجات ({{ $delivery->order->items->count() }})</h6>
        @foreach($delivery->order->items as $item)
            <div class="d-flex justify-content-between py-2 {{ ! $loop->last ? 'border-bottom' : '' }}">
                <div>
                    <div class="fw-semibold">{{ $item->product->name }}</div>
                    <small class="text-muted">{{ $item->quantity }} × {{ number_format((float) $item->price, 2) }}</small>
                </div>
                <div class="fw-bold">{{ number_format((float) $item->total, 2) }}</div>
            </div>
        @endforeach
        <div class="d-flex justify-content-between pt-2 border-top mt-2">
            <strong>الإجمالي</strong>
            <strong class="text-success">{{ number_format((float) $delivery->order->net_total, 2) }} ج.م</strong>
        </div>
    </div>

    {{-- Timeline --}}
    <div class="delivery-card mt-2">
        <h6 class="fw-bold mb-2"><i class="bi bi-clock-history text-primary"></i> المراحل</h6>
        <ul class="list-unstyled mb-0">
            @if($delivery->assigned_at)
            <li class="d-flex gap-2 mb-2">
                <i class="bi bi-check-circle-fill text-warning mt-1"></i>
                <div>
                    <small class="text-muted d-block">تم التعيين</small>
                    <span>{{ $delivery->assigned_at->format('d/m/Y H:i') }}</span>
                </div>
            </li>
            @endif
            @if($delivery->started_at)
            <li class="d-flex gap-2 mb-2">
                <i class="bi bi-play-circle-fill text-primary mt-1"></i>
                <div>
                    <small class="text-muted d-block">بدء التوصيل</small>
                    <span>{{ $delivery->started_at->format('d/m/Y H:i') }}</span>
                </div>
            </li>
            @endif
            @if($delivery->delivered_at)
            <li class="d-flex gap-2 mb-2">
                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                <div>
                    <small class="text-muted d-block">تم التسليم</small>
                    <span>{{ $delivery->delivered_at->format('d/m/Y H:i') }}</span>
                    @if($delivery->duration_minutes)
                        <small class="text-muted">(استغرق {{ $delivery->duration_minutes }} دقيقة)</small>
                    @endif
                </div>
            </li>
            @endif
            @if($delivery->status === 'failed' && $delivery->failure_reason)
            <li class="d-flex gap-2 mb-2">
                <i class="bi bi-x-circle-fill text-danger mt-1"></i>
                <div>
                    <small class="text-muted d-block">فشل التسليم</small>
                    <span class="text-danger">{{ $delivery->failure_reason }}</span>
                </div>
            </li>
            @endif
        </ul>
    </div>

    {{-- Signature / proof --}}
    @if($delivery->signature || $delivery->photo)
    <div class="delivery-card mt-2">
        <h6 class="fw-bold mb-2"><i class="bi bi-pen text-primary"></i> إثبات التسليم</h6>
        @if($delivery->signature)
            <div class="mb-2">
                <small class="text-muted d-block mb-1">توقيع العميل:</small>
                <img src="{{ asset('storage/'.$delivery->signature) }}"
                     alt="توقيع"
                     style="max-width: 100%; border: 1px solid #e5e7eb; border-radius: 8px;">
            </div>
        @endif
        @if($delivery->photo)
            <div>
                <small class="text-muted d-block mb-1">صورة:</small>
                <img src="{{ asset('storage/'.$delivery->photo) }}"
                     alt="صورة"
                     style="max-width: 100%; border-radius: 8px;">
            </div>
        @endif
    </div>
    @endif

    {{-- Notes --}}
    @if($delivery->notes)
    <div class="delivery-card mt-2">
        <h6 class="fw-bold mb-2"><i class="bi bi-sticky text-primary"></i> ملاحظات</h6>
        <p class="mb-0 small text-secondary">{{ $delivery->notes }}</p>
    </div>
    @endif

    {{-- Live actions if still in progress --}}
    @if($delivery->status === 'assigned')
        <button class="btn-action mt-3 btn-start-detail" data-id="{{ $delivery->id }}">
            <i class="bi bi-play-fill"></i> ابدأ التوصيل
        </button>
    @elseif($delivery->status === 'in_progress')
        <div class="d-grid gap-2 mt-3">
            <a href="{{ route('deliveries.driver') }}" class="btn-action success">
                <i class="bi bi-arrow-right"></i> الرجوع لإكمال التسليم
            </a>
        </div>
    @endif

@else
    {{-- ═══════════════════════════════════════════════════════════
         Admin desktop view (original)
         ═══════════════════════════════════════════════════════════ --}}
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <i class="bi bi-truck"></i> {{ $delivery->delivery_number }}
                <span class="badge bg-{{ $st['cls'] }} ms-2">
                    <i class="bi bi-{{ $st['icon'] }}"></i> {{ $st['label'] }}
                </span>
            </h6>
            <a href="{{ route('deliveries.index') }}" class="btn btn-sm btn-light">
                <i class="bi bi-arrow-right"></i> رجوع
            </a>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <p class="mb-1"><strong>الطلب:</strong>
                        <a href="{{ route('orders.show', $delivery->order) }}">{{ $delivery->order->order_number }}</a>
                    </p>
                    <p class="mb-1"><strong>العميل:</strong>
                        <a href="{{ route('customers.show', $delivery->order->customer) }}">{{ $delivery->order->customer->name }}</a>
                    </p>
                    <p class="mb-1"><strong>الهاتف:</strong> {{ $delivery->order->customer->phone ?? '-' }}</p>
                    <p class="mb-1"><strong>السائق:</strong> {{ $delivery->driver?->name ?? '-' }}</p>
                    <p class="mb-1"><strong>السيارة:</strong> {{ $delivery->vehicle_number ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>تاريخ التعيين:</strong> {{ $delivery->assigned_at?->format('d/m/Y H:i') ?? '-' }}</p>
                    <p class="mb-1"><strong>بدء التوصيل:</strong> {{ $delivery->started_at?->format('d/m/Y H:i') ?? '-' }}</p>
                    <p class="mb-1"><strong>تم التسليم:</strong> {{ $delivery->delivered_at?->format('d/m/Y H:i') ?? '-' }}</p>
                    @if($delivery->duration_minutes)
                        <p class="mb-1"><strong>المدة:</strong> {{ $delivery->duration_minutes }} دقيقة</p>
                    @endif
                    <p class="mb-1"><strong>إجمالي الطلب:</strong> {{ number_format((float) $delivery->order->net_total, 2) }} ج.م</p>
                </div>
            </div>

            @if($delivery->status === 'failed' && $delivery->failure_reason)
            <div class="alert alert-danger">
                <strong>سبب الفشل:</strong> {{ $delivery->failure_reason }}
            </div>
            @endif

            @if($delivery->notes)
            <div class="alert alert-light">
                <strong>ملاحظات:</strong> {{ $delivery->notes }}
            </div>
            @endif

            <div class="row g-3">
                @if($delivery->signature)
                <div class="col-md-6">
                    <strong>توقيع العميل:</strong><br>
                    <img src="{{ asset('storage/'.$delivery->signature) }}"
                         alt="توقيع" style="max-width: 100%; border: 1px solid #ddd; border-radius: 6px; margin-top: .5rem;">
                </div>
                @endif
                @if($delivery->photo)
                <div class="col-md-6">
                    <strong>صورة:</strong><br>
                    <img src="{{ asset('storage/'.$delivery->photo) }}"
                         alt="صورة" style="max-width: 100%; border-radius: 6px; margin-top: .5rem;">
                </div>
                @endif
            </div>
        </div>
    </div>
@endif

@endsection

@if($isDriverOnly ?? false)
@push('scripts')
<script>
$('.btn-start-detail').on('click', function () {
    const $btn = $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> جارٍ تحديد الموقع...');
    const id = $btn.data('id');

    const send = (lat, lng) => {
        $.post("{{ url('driver-app/deliveries') }}/" + id + "/start", { _token: "{{ csrf_token() }}", lat, lng })
            .done(() => { toastr.success('تم بدء التوصيل'); setTimeout(() => location.reload(), 600); })
            .fail(() => { toastr.error('خطأ في بدء التوصيل'); $btn.prop('disabled', false).html('<i class="bi bi-play-fill"></i> ابدأ التوصيل'); });
    };

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            pos => send(pos.coords.latitude, pos.coords.longitude),
            () => send(null, null),
            { timeout: 8000 }
        );
    } else { send(null, null); }
});
</script>
@endpush
@endif
