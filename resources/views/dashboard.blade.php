@extends('layouts.admin')
@section('title', __('Dashboard'))
@section('page_title', __('Dashboard'))

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card kpi-card text-bg-primary shadow-sm">
            <div class="card-body">
                <small>مبيعات اليوم</small>
                <h3 class="mb-0">{{ number_format($kpis['sales_today'], 0) }}</h3>
                <small><i class="bi bi-graph-up"></i> ج.م</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card kpi-card text-bg-success shadow-sm">
            <div class="card-body">
                <small>مبيعات الشهر</small>
                <h3 class="mb-0">{{ number_format($kpis['sales_month'], 0) }}</h3>
                <small><i class="bi bi-calendar-month"></i> ج.م</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card kpi-card text-bg-warning shadow-sm">
            <div class="card-body">
                <small>طلبات معلقة</small>
                <h3 class="mb-0">{{ $kpis['orders_pending'] }}</h3>
                <small><i class="bi bi-clock"></i> طلب</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card kpi-card text-bg-info shadow-sm">
            <div class="card-body">
                <small>تم توصيلها (الشهر)</small>
                <h3 class="mb-0">{{ $kpis['orders_delivered'] }}</h3>
                <small><i class="bi bi-truck"></i> طلب</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm"><div class="card-body">
            <small>عملاء نشطين</small><h4 class="mb-0">{{ $kpis['customers_active'] }}</h4>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm"><div class="card-body">
            <small class="text-warning">منتجات مخزون منخفض</small><h4 class="mb-0">{{ $kpis['low_stock'] }}</h4>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm"><div class="card-body">
            <small class="text-danger">إجمالي الذمم المتأخرة</small><h4 class="mb-0">{{ number_format($kpis['overdue_total'], 2) }}</h4>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm"><div class="card-body">
            <small>طلبات مؤكدة</small><h4 class="mb-0">{{ $kpis['orders_confirmed'] }}</h4>
        </div></div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header"><h6 class="mb-0">المبيعات - آخر 30 يوم</h6></div>
            <div class="card-body"><canvas id="salesChart"></canvas></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header"><h6 class="mb-0">المبيعات حسب المنطقة</h6></div>
            <div class="card-body"><canvas id="zoneChart"></canvas></div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header"><h6 class="mb-0">أعلى 5 منتجات (الشهر)</h6></div>
            <div class="card-body"><canvas id="productsChart"></canvas></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header"><h6 class="mb-0">أعلى 5 مناديب (الشهر)</h6></div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead><tr><th>المندوب</th><th>الطلبات</th><th>الإجمالي</th></tr></thead>
                    <tbody>
                    @forelse($topSalesmen as $s)
                        <tr><td>{{ $s->name }}</td><td>{{ $s->count }}</td><td>{{ number_format($s->total, 2) }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted">لا توجد بيانات</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    new Chart(document.getElementById('salesChart'), {
        type: 'line',
        data: {
            labels: @json($sales30->pluck('date')),
            datasets: [{ label: 'مبيعات', data: @json($sales30->pluck('total')), tension: 0.3, borderColor: '#0d6efd', fill: true, backgroundColor: 'rgba(13,110,253,0.1)' }]
        }
    });

    new Chart(document.getElementById('zoneChart'), {
        type: 'doughnut',
        data: {
            labels: @json($salesByZone->pluck('zone')),
            datasets: [{ data: @json($salesByZone->pluck('total')), backgroundColor: ['#0d6efd','#198754','#ffc107','#dc3545','#6f42c1','#20c997'] }]
        }
    });

    new Chart(document.getElementById('productsChart'), {
        type: 'bar',
        data: {
            labels: @json($topProducts->pluck('name')),
            datasets: [{ label: 'الكمية', data: @json($topProducts->pluck('qty')), backgroundColor: '#0d6efd' }]
        }
    });
});
</script>
@endpush
