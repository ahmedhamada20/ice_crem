@extends('layouts.admin')
@section('title', 'خريطة المناديب')
@section('page_title', 'موقع المناديب الحالي')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <div id="map" style="height: 70vh; border-radius: 8px;"></div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
$(function () {
    const map = L.map('map').setView([30.0444, 31.2357], 11);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);

    @foreach($drivers as $driver)
        @foreach($driver->deliveries as $del)
            @if($del->order->customer->location_lat && $del->order->customer->location_lng)
            L.marker([{{ $del->order->customer->location_lat }}, {{ $del->order->customer->location_lng }}])
                .addTo(map)
                .bindPopup(`<strong>{{ $del->order->customer->name }}</strong><br>السائق: {{ $driver->name }}<br>الحالة: {{ $del->status }}`);
            @endif
        @endforeach
    @endforeach
});
</script>
@endpush
