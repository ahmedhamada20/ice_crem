@extends('layouts.admin')
@section('title', __('Dashboard'))
@section('page_title', 'لوحة التحكم - ' . auth()->user()->name)

@section('content')
<div class="alert alert-success py-2 mb-3">
    <i class="bi bi-person-badge"></i> هذه إحصائياتك الشخصية كمندوب
    <a href="{{ route('orders.create') }}" class="btn btn-sm btn-success ms-3">
        <i class="bi bi-plus"></i> طلب جديد
    </a>
    <a href="{{ route('visits.index') }}" class="btn btn-sm btn-outline-success">
        <i class="bi bi-geo-alt"></i> تسجيل زيارة
    </a>
</div>

<div class="row g-3 mb-4">
    @include('dashboard._kpi', ['label' => 'طلباتي اليوم',   'value' => $kpis['orders_today'],                   'color' => 'primary', 'icon' => 'cart',          'sub' => 'طلب'])
    @include('dashboard._kpi', ['label' => 'طلباتي الشهر',   'value' => $kpis['orders_month'],                   'color' => 'info',    'icon' => 'cart-check',    'sub' => 'طلب'])
    @include('dashboard._kpi', ['label' => 'مبيعاتي الشهر',  'value' => number_format($kpis['sales_month'], 0),  'color' => 'success', 'icon' => 'cash-stack',    'sub' => 'ج.م'])
    @include('dashboard._kpi', ['label' => 'طلبات معلقة',    'value' => $kpis['pending_orders'],                  'color' => 'warning', 'icon' => 'clock',         'sub' => 'في الانتظار'])
</div>

<div class="row g-3 mb-4">
    @include('dashboard._kpi', ['label' => 'زيارات اليوم',  'value' => $kpis['visits_today'],   'color' => 'light text-dark', 'col' => 4, 'icon' => 'geo-alt', 'sub' => 'زيارة'])
    @include('dashboard._kpi', ['label' => 'زيارات الشهر',  'value' => $kpis['visits_month'],   'color' => 'light text-dark', 'col' => 4, 'icon' => 'calendar', 'sub' => 'زيارة'])
    @include('dashboard._kpi', ['label' => 'عملاء منطقتي',  'value' => $kpis['customers_count'],'color' => 'light text-dark', 'col' => 4, 'icon' => 'people',  'sub' => 'عميل نشط'])
</div>

<div class="row g-3 mb-4">
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header"><h6 class="mb-0">مبيعاتي - آخر 30 يوم</h6></div>
            <div class="card-body"><canvas id="myOrdersChart"></canvas></div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-header"><h6 class="mb-0">أكثر 5 منتجات بعتها (الشهر)</h6></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                    @forelse($topProducts as $p)
                        <tr>
                            <td>{{ $p->name }}</td>
                            <td class="text-end"><span class="badge bg-primary">{{ $p->qty }}</span></td>
                            <td class="text-end fw-bold">{{ number_format($p->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td class="text-center text-muted">لا توجد بيانات</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header"><h6 class="mb-0">آخر طلباتي</h6></div>
    <div class="card-body">
        <table class="table table-sm table-hover">
            <thead><tr><th>رقم الطلب</th><th>العميل</th><th>التاريخ</th><th>{{ __('Net Total') }}</th><th>{{ __('Status') }}</th></tr></thead>
            <tbody>
            @forelse($recentOrders as $o)
                <tr style="cursor:pointer" onclick="location.href='{{ route('orders.show', $o) }}'">
                    <td>{{ $o->order_number }}</td>
                    <td>{{ $o->customer?->name }}</td>
                    <td>{{ $o->order_date->format('d/m/Y') }}</td>
                    <td class="text-end">{{ number_format((float) $o->net_total, 2) }}</td>
                    <td>{!! $o->status_badge !!}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted">ليس لديك طلبات بعد</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
new Chart(document.getElementById('myOrdersChart'), {
    type: 'line',
    data: {
        labels: @json($myOrders30->pluck('date')),
        datasets: [{ label: 'مبيعاتي', data: @json($myOrders30->pluck('total')), tension: 0.3, borderColor: '#198754', fill: true, backgroundColor: 'rgba(25,135,84,.1)' }]
    }
});
</script>
@endpush
