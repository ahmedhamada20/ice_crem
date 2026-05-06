@extends('layouts.admin')
@section('title', __('Dashboard'))
@section('page_title', 'لوحة المخزن')

@section('content')
<div class="alert alert-info py-2 mb-3">
    <i class="bi bi-stack"></i> لوحة إدارة المخزون
    <a href="{{ route('stock.inventory') }}" class="btn btn-sm btn-info ms-3">
        <i class="bi bi-clipboard-check"></i> جرد المخزون
    </a>
    <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-dark">
        <i class="bi bi-box-seam"></i> المنتجات
    </a>
</div>

<div class="row g-3 mb-4">
    @include('dashboard._kpi', ['label' => 'إجمالي المنتجات',   'value' => $kpis['total_products'],                       'color' => 'primary', 'icon' => 'box-seam',  'sub' => 'منتج'])
    @include('dashboard._kpi', ['label' => 'إجمالي الكميات',     'value' => number_format($kpis['total_stock_units']),     'color' => 'info',    'icon' => 'stack',     'sub' => 'وحدة'])
    @include('dashboard._kpi', ['label' => 'مخزون منخفض',        'value' => $kpis['low_stock_count'],                      'color' => 'warning', 'icon' => 'exclamation-triangle', 'sub' => 'منتج'])
    @include('dashboard._kpi', ['label' => 'نافد المخزون',       'value' => $kpis['out_of_stock'],                         'color' => 'danger',  'icon' => 'x-circle',  'sub' => 'منتج'])
</div>

<div class="row g-3 mb-4">
    @include('dashboard._kpi', ['label' => 'قيمة المخزون',       'value' => number_format($kpis['stock_value'], 0),  'color' => 'success', 'col' => 6, 'icon' => 'cash-coin', 'sub' => 'ج.م (بسعر التكلفة)'])
    @include('dashboard._kpi', ['label' => 'حركات اليوم',        'value' => $kpis['movements_today'],                'color' => 'light text-dark', 'col' => 6, 'icon' => 'arrow-left-right', 'sub' => 'حركة'])
</div>

<div class="row g-3 mb-4">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-header"><h6 class="mb-0">توزيع المخزون على المستودعات</h6></div>
            <div class="card-body"><canvas id="warehouseChart"></canvas></div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="bi bi-exclamation-triangle"></i> منتجات بمخزون منخفض</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>الكود</th><th>المنتج</th><th>المستودع</th><th class="text-end">الكمية</th><th class="text-end">الحد الأدنى</th></tr></thead>
                    <tbody>
                    @forelse($lowStockItems as $s)
                        <tr>
                            <td>{{ $s->product?->code }}</td>
                            <td>{{ $s->product?->name }}</td>
                            <td>{{ $s->warehouse?->name }}</td>
                            <td class="text-end text-danger fw-bold">{{ $s->quantity }}</td>
                            <td class="text-end text-muted">{{ $s->product?->min_stock }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-success">✓ كل المخزون فوق الحد الأدنى</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-danger text-white">
                <h6 class="mb-0"><i class="bi bi-x-circle"></i> منتجات نافدة المخزون</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>الكود</th><th>المنتج</th><th>المستودع</th></tr></thead>
                    <tbody>
                    @forelse($outOfStockItems as $s)
                        <tr>
                            <td>{{ $s->product?->code }}</td>
                            <td>{{ $s->product?->name }}</td>
                            <td>{{ $s->warehouse?->name }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-success">✓ لا يوجد منتجات نافدة</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header"><h6 class="mb-0">آخر حركات المخزون</h6></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>التاريخ</th><th>المنتج</th><th>النوع</th><th class="text-end">الكمية</th></tr></thead>
                    <tbody>
                    @forelse($recentMovements as $m)
                        @php
                            $cls = match ($m->type) { 'in' => 'success', 'out' => 'danger', 'transfer' => 'info', 'adjustment' => 'warning text-dark', default => 'secondary' };
                            $lbl = match ($m->type) { 'in' => 'وارد', 'out' => 'صادر', 'transfer' => 'تحويل', 'adjustment' => 'جرد', default => $m->type };
                        @endphp
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($m->created_at)->format('d/m H:i') }}</td>
                            <td>{{ $m->product_name }}</td>
                            <td><span class="badge bg-{{ $cls }}">{{ $lbl }}</span></td>
                            <td class="text-end">{{ $m->quantity }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">لا توجد حركات</td></tr>
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
new Chart(document.getElementById('warehouseChart'), {
    type: 'doughnut',
    data: {
        labels: @json($byWarehouse->pluck('name')),
        datasets: [{ data: @json($byWarehouse->pluck('qty')), backgroundColor: ['#0d6efd','#198754','#ffc107','#dc3545','#6f42c1','#20c997'] }]
    }
});
</script>
@endpush
