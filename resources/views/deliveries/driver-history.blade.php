@extends('layouts.driver')
@section('title', 'سجل التوصيلات')

@section('content')

{{-- Period filter chips --}}
<div class="d-flex gap-2 mb-3" style="overflow-x: auto; padding-bottom: .25rem;">
    @php
        $tabs = [
            'today' => 'اليوم',
            'week'  => 'الأسبوع',
            'month' => 'الشهر',
            'all'   => 'الكل',
        ];
    @endphp
    @foreach($tabs as $key => $label)
        <a href="{{ route('deliveries.driver.history', ['period' => $key]) }}"
           class="period-chip {{ $period === $key ? 'active' : '' }}">{{ $label }}</a>
    @endforeach
</div>

{{-- Stats --}}
<div class="stats-row mb-2" style="padding: 0;">
    <div class="stat-card success">
        <div class="num">{{ $stats['delivered'] }}</div>
        <div class="lbl">تم التسليم</div>
    </div>
    <div class="stat-card danger">
        <div class="num">{{ $stats['failed'] }}</div>
        <div class="lbl">فشل التسليم</div>
    </div>
    <div class="stat-card">
        <div class="num">{{ number_format($stats['revenue'], 0) }}</div>
        <div class="lbl">إجمالي (ج.م)</div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mt-3 mb-2 px-1">
    <h6 class="mb-0 fw-bold text-dark">
        <i class="bi bi-clock-history"></i> آخر {{ $deliveries->count() }} توصيلة
    </h6>
    @if($stats['total'] > 0)
        <small class="text-muted">معدل النجاح: {{ round($stats['delivered'] / $stats['total'] * 100) }}%</small>
    @endif
</div>

{{-- History list --}}
@forelse($deliveries as $d)
    <a href="{{ route('deliveries.show', $d) }}" class="text-decoration-none text-dark">
        <div class="delivery-card {{ $d->status === 'delivered' ? 'delivered' : '' }}"
             style="{{ $d->status === 'failed' ? 'border-right-color:#dc2626;opacity:.85' : '' }}">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <div class="flex-grow-1">
                    <div class="order-num">{{ $d->order->order_number }}</div>
                    <div class="customer">{{ $d->order->customer->name }}</div>
                </div>
                @if($d->status === 'delivered')
                    <span class="pill delivered"><i class="bi bi-check-circle"></i> تم</span>
                @elseif($d->status === 'failed')
                    <span class="pill" style="background:#fee2e2;color:#991b1b;"><i class="bi bi-x-circle"></i> فشل</span>
                @else
                    <span class="pill" style="background:#fed7aa;color:#9a3412;"><i class="bi bi-arrow-counterclockwise"></i> مرتجع</span>
                @endif
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <div class="meta">
                    <i class="bi bi-clock"></i>
                    @if($d->status === 'delivered' && $d->delivered_at)
                        {{ $d->delivered_at->diffForHumans() }}
                    @elseif($d->updated_at)
                        {{ $d->updated_at->diffForHumans() }}
                    @endif
                    @if($d->duration_minutes)
                        <span class="ms-2"><i class="bi bi-stopwatch"></i> {{ $d->duration_minutes }} د</span>
                    @endif
                </div>
                @if($d->status === 'delivered')
                    <span class="total">{{ number_format((float) $d->order->net_total, 0) }} ج.م</span>
                @elseif($d->failure_reason)
                    <small class="text-danger">{{ Str::limit($d->failure_reason, 30) }}</small>
                @endif
            </div>
        </div>
    </a>
@empty
    <div class="empty">
        <i class="bi bi-inbox"></i>
        <h6 class="mt-3">لا يوجد سجل توصيلات</h6>
        <p class="small mb-0">لم يتم تسجيل توصيلات في هذه الفترة</p>
    </div>
@endforelse

@endsection

@push('styles')
<style>
.period-chip {
    flex-shrink: 0;
    padding: .45rem 1rem;
    border-radius: 999px;
    background: white;
    color: #6b7280;
    text-decoration: none;
    font-size: .85rem;
    font-weight: 600;
    border: 1.5px solid #e5e7eb;
    transition: all .15s;
    white-space: nowrap;
}
.period-chip.active {
    background: linear-gradient(135deg, var(--brand), var(--brand-2));
    color: white;
    border-color: transparent;
}
.period-chip:hover { color: var(--brand); }
.period-chip.active:hover { color: white; }
</style>
@endpush
