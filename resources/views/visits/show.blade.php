@extends('layouts.admin')
@section('title', 'زيارة - ' . $visit->customer?->name)
@section('page_title', 'تفاصيل الزيارة')

@section('content')
<div class="row g-3">
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header"><h6 class="mb-0">معلومات الزيارة</h6></div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6"><strong>{{ __('Customer') }}:</strong> <a href="{{ route('customers.show', $visit->customer) }}">{{ $visit->customer?->name }}</a></div>
                    <div class="col-md-6"><strong>{{ __('Salesman') }}:</strong> {{ $visit->salesman?->name }}</div>
                    <div class="col-md-6 mt-2"><strong>التاريخ:</strong> {{ $visit->visit_date?->format('d/m/Y') }}</div>
                    <div class="col-md-6 mt-2"><strong>المدة:</strong> {{ $visit->duration_minutes ? $visit->duration_minutes . ' دقيقة' : '—' }}</div>
                    <div class="col-md-6 mt-2"><strong>دخول:</strong> {{ $visit->check_in?->format('H:i') ?? '—' }}</div>
                    <div class="col-md-6 mt-2"><strong>خروج:</strong> {{ $visit->check_out?->format('H:i') ?? '—' }}</div>
                </div>

                <div class="mb-3">
                    <strong>النتيجة:</strong>
                    @php $resCls = match($visit->result) { 'order_placed' => 'success', 'no_order' => 'secondary', 'rescheduled' => 'warning text-dark', 'closed' => 'danger', default => 'light text-dark' }; @endphp
                    <span class="badge bg-{{ $resCls }}">{{ $visit->result ?? '—' }}</span>
                </div>

                @if($visit->order)
                    <div class="alert alert-success">
                        <i class="bi bi-cart"></i> طلب تم إنشاؤه: <a href="{{ route('orders.show', $visit->order) }}">{{ $visit->order->order_number }}</a>
                    </div>
                @endif

                @if($visit->notes)
                    <div class="alert alert-light"><strong>ملاحظات:</strong> {{ $visit->notes }}</div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-5">
        @if($visit->check_in_lat && $visit->check_in_lng)
            <div class="card shadow-sm">
                <div class="card-header"><h6 class="mb-0">موقع الدخول</h6></div>
                <div class="card-body">
                    <div id="visitMap" style="height: 300px; border-radius: 8px;"></div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@if($visit->check_in_lat && $visit->check_in_lng)
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
$(function () {
    const map = L.map('visitMap').setView([{{ $visit->check_in_lat }}, {{ $visit->check_in_lng }}], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);
    L.marker([{{ $visit->check_in_lat }}, {{ $visit->check_in_lng }}]).addTo(map).bindPopup('دخول: {{ $visit->check_in?->format("H:i") }}');
    @if($visit->check_out_lat && $visit->check_out_lng)
    L.marker([{{ $visit->check_out_lat }}, {{ $visit->check_out_lng }}]).addTo(map).bindPopup('خروج: {{ $visit->check_out?->format("H:i") }}');
    @endif
});
</script>
@endpush
@endif
