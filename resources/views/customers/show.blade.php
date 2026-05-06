@extends('layouts.admin')
@section('title', $customer->name)
@section('page_title', __('Customer') . ' - ' . $customer->name)

@section('content')
<div class="row g-3">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">{{ $customer->name }}</h5>
                <p class="text-muted mb-1">{{ $customer->code }}</p>
                <hr>
                <p><strong>{{ __('Phone') }}:</strong> {{ $customer->phone ?? '-' }}</p>
                <p><strong>{{ __('Type') }}:</strong> {{ __($customer->type) }}</p>
                <p><strong>{{ __('Zone') }}:</strong> {{ $customer->zone?->name ?? '-' }}</p>
                <p><strong>{{ __('Credit Limit') }}:</strong> {{ number_format((float) $customer->credit_limit, 2) }}</p>
                <p><strong>{{ __('Balance') }}:</strong> {{ number_format((float) $customer->balance, 2) }}</p>
                <p><strong>{{ __('Status') }}:</strong> {!! $customer->status_badge !!}</p>
                <p><strong>{{ __('Address') }}:</strong> {{ $customer->address ?? '-' }}</p>

                <a href="{{ route('customers.statement', $customer) }}" class="btn btn-info btn-sm w-100 mt-2">
                    <i class="bi bi-file-text"></i> كشف حساب
                </a>
            </div>
        </div>

        @if($customer->location_lat && $customer->location_lng)
        <div class="card shadow-sm mt-3">
            <div class="card-body">
                <h6>الموقع</h6>
                <div id="customerMap" style="height: 250px; border-radius: 8px;"></div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header"><h6 class="mb-0">حركة الحساب الأخيرة</h6></div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>التاريخ</th>
                            <th>النوع</th>
                            <th>المرجع</th>
                            <th class="text-end">مدين</th>
                            <th class="text-end">دائن</th>
                            <th class="text-end">الرصيد</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($statement as $row)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
                                <td>{{ $row['type'] === 'invoice' ? 'فاتورة' : 'دفعة' }}</td>
                                <td>{{ $row['ref'] }}</td>
                                <td class="text-end">{{ number_format($row['debit'], 2) }}</td>
                                <td class="text-end">{{ number_format($row['credit'], 2) }}</td>
                                <td class="text-end">{{ number_format($row['balance'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">{{ __('No data available') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@if($customer->location_lat && $customer->location_lng)
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
$(function () {
    const map = L.map('customerMap').setView([{{ $customer->location_lat }}, {{ $customer->location_lng }}], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);
    L.marker([{{ $customer->location_lat }}, {{ $customer->location_lng }}]).addTo(map).bindPopup("{{ $customer->name }}");
});
</script>
@endpush
@endif
