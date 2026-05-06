@extends(($isDriverOnly ?? false) ? 'layouts.driver' : 'layouts.admin')
@section('title', ($isDriverOnly ?? false) ? 'الخريطة' : 'خريطة المناديب')
@section('page_title', 'موقع المناديب الحالي')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
    {{-- Driver layout: full-height map --}}
    .app-main #map { height: calc(100vh - 220px); border-radius: 12px; }
</style>
@endpush

@section('content')
@if($isDriverOnly ?? false)
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0 fw-bold"><i class="bi bi-map"></i> توصيلاتي على الخريطة</h6>
        <button class="btn btn-sm btn-light" id="btnMyLocation"><i class="bi bi-crosshair"></i></button>
    </div>
    <div id="map" style="height: calc(100vh - 240px); border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,.05);"></div>
@else
    <div class="card shadow-sm">
        <div class="card-body">
            <div id="map" style="height: 70vh; border-radius: 8px;"></div>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
$(function () {
    const map = L.map('map').setView([30.0444, 31.2357], 11);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap', maxZoom: 19 }).addTo(map);

    const bounds = [];

    @foreach($drivers as $driver)
        @foreach($driver->deliveries as $del)
            @if($del->order->customer->location_lat && $del->order->customer->location_lng)
            (function () {
                const lat = {{ $del->order->customer->location_lat }};
                const lng = {{ $del->order->customer->location_lng }};
                const m = L.marker([lat, lng]).addTo(map)
                    .bindPopup(`<strong>{{ $del->order->customer->name }}</strong><br>
                                طلب: {{ $del->order->order_number }}<br>
                                {{ $del->order->customer->phone }}<br>
                                <a href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}" target="_blank">الاتجاهات</a>`);
                bounds.push([lat, lng]);
            })();
            @endif
        @endforeach
    @endforeach

    if (bounds.length) map.fitBounds(bounds, { padding: [40, 40] });

    @if($isDriverOnly ?? false)
    // Show driver's current position
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(pos => {
            const me = L.circleMarker([pos.coords.latitude, pos.coords.longitude], {
                radius: 8, color: '#6366f1', fillColor: '#6366f1', fillOpacity: 1
            }).addTo(map).bindPopup('موقعي الحالي');
        });
    }

    $('#btnMyLocation').on('click', () => {
        navigator.geolocation.getCurrentPosition(pos => {
            map.setView([pos.coords.latitude, pos.coords.longitude], 16);
        });
    });
    @endif
});
</script>
@endpush
