@extends('layouts.admin')
@section('title', __('Dashboard'))
@section('page_title', __('Dashboard') . ' - ' . $kpis['zone_name'])

@section('content')
<div class="alert alert-info py-2 mb-3">
    <i class="bi bi-map"></i> أنت تشاهد بيانات منطقة <strong>{{ $kpis['zone_name'] }}</strong> فقط
</div>

<div class="row g-3 mb-4">
    @include('dashboard._kpi', ['label' => 'مبيعات اليوم',     'value' => number_format($kpis['sales_today'], 0),  'color' => 'primary', 'icon' => 'graph-up',  'sub' => 'ج.م'])
    @include('dashboard._kpi', ['label' => 'مبيعات الشهر',     'value' => number_format($kpis['sales_month'], 0),  'color' => 'success', 'icon' => 'calendar-month', 'sub' => 'ج.م'])
    @include('dashboard._kpi', ['label' => 'طلبات معلقة',      'value' => $kpis['orders_pending'],                  'color' => 'warning', 'icon' => 'clock',     'sub' => 'طلب'])
    @include('dashboard._kpi', ['label' => 'الذمم المتأخرة',   'value' => number_format($kpis['overdue_total'], 0), 'color' => 'danger',  'icon' => 'exclamation-triangle', 'sub' => 'ج.م'])
</div>

<div class="row g-3 mb-4">
    @include('dashboard._kpi', ['label' => 'عملاء المنطقة',     'value' => $kpis['customers_count'],                'color' => 'light text-dark', 'col' => 4, 'icon' => 'people', 'sub' => 'عميل'])
    @include('dashboard._kpi', ['label' => 'مناديب المنطقة',    'value' => $kpis['salesmen_count'],                 'color' => 'light text-dark', 'col' => 4, 'icon' => 'person-badge', 'sub' => 'مندوب'])
    @include('dashboard._kpi', ['label' => 'إجمالي طلبات اليوم','value' => $kpis['orders_pending'],                  'color' => 'light text-dark', 'col' => 4, 'icon' => 'cart',  'sub' => 'تحت المتابعة'])
</div>

<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header"><h6 class="mb-0">المبيعات في المنطقة - آخر 30 يوم</h6></div>
            <div class="card-body"><canvas id="salesChart"></canvas></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header"><h6 class="mb-0">أعلى 5 عملاء (الشهر)</h6></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                    @forelse($topCustomers as $c)
                        <tr><td>{{ $c->name }}</td><td class="text-end fw-bold text-success">{{ number_format($c->total, 2) }}</td></tr>
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
    <div class="card-header"><h6 class="mb-0">أداء مناديب المنطقة (الشهر)</h6></div>
    <div class="card-body">
        <table class="table table-sm">
            <thead><tr><th>المندوب</th><th class="text-end">عدد الطلبات</th><th class="text-end">الإجمالي</th></tr></thead>
            <tbody>
            @forelse($salesmenPerf as $s)
                <tr><td>{{ $s->name }}</td><td class="text-end">{{ $s->count }}</td><td class="text-end fw-bold">{{ number_format($s->total, 2) }}</td></tr>
            @empty
                <tr><td colspan="3" class="text-center text-muted">لا يوجد نشاط</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
new Chart(document.getElementById('salesChart'), {
    type: 'line',
    data: {
        labels: @json($sales30->pluck('date')),
        datasets: [{ label: 'المبيعات', data: @json($sales30->pluck('total')), tension: 0.3, borderColor: '#0d6efd', fill: true, backgroundColor: 'rgba(13,110,253,.1)' }]
    }
});
</script>
@endpush
