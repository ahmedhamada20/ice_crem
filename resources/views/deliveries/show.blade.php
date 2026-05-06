@extends('layouts.admin')
@section('title', $delivery->delivery_number)

@section('content')
<div class="card shadow-sm">
    <div class="card-header"><h6>{{ $delivery->delivery_number }}</h6></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>الطلب:</strong> {{ $delivery->order->order_number }}</p>
                <p><strong>العميل:</strong> {{ $delivery->order->customer->name }}</p>
                <p><strong>السائق:</strong> {{ $delivery->driver?->name ?? '-' }}</p>
                <p><strong>السيارة:</strong> {{ $delivery->vehicle_number ?? '-' }}</p>
                <p><strong>الحالة:</strong> {{ $delivery->status }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>تاريخ التعيين:</strong> {{ $delivery->assigned_at?->format('d/m/Y H:i') ?? '-' }}</p>
                <p><strong>بدء التوصيل:</strong> {{ $delivery->started_at?->format('d/m/Y H:i') ?? '-' }}</p>
                <p><strong>تم التسليم:</strong> {{ $delivery->delivered_at?->format('d/m/Y H:i') ?? '-' }}</p>
                @if($delivery->signature)
                    <div><strong>التوقيع:</strong><br><img src="{{ asset('storage/'.$delivery->signature) }}" style="max-width: 200px; border: 1px solid #ddd;"></div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
