@extends('layouts.admin')
@section('title', __('Reports'))
@section('page_title', __('Reports'))

@section('content')
<div class="row g-3">
    @php $reports = [
        ['route' => 'reports.sales',      'icon' => 'bi-graph-up',      'title' => 'تقرير المبيعات'],
        ['route' => 'reports.products',   'icon' => 'bi-box-seam',      'title' => 'المنتجات الأكثر مبيعاً'],
        ['route' => 'reports.customers',  'icon' => 'bi-people',        'title' => 'تقرير العملاء'],
        ['route' => 'reports.salesmen',   'icon' => 'bi-person-badge',  'title' => 'أداء المناديب'],
        ['route' => 'reports.stock',      'icon' => 'bi-stack',         'title' => 'تقرير المخزون'],
        ['route' => 'reports.aging',      'icon' => 'bi-clock-history', 'title' => 'الذمم المتأخرة (Aging)'],
        ['route' => 'reports.profit',     'icon' => 'bi-currency-dollar','title' => 'تقرير الأرباح'],
        ['route' => 'reports.visits',     'icon' => 'bi-geo-alt',       'title' => 'تقرير الزيارات'],
        ['route' => 'reports.deliveries', 'icon' => 'bi-truck',         'title' => 'تقرير التوصيل'],
    ]; @endphp

    @foreach($reports as $r)
    <div class="col-md-4">
        <a href="{{ route($r['route']) }}" class="card shadow-sm text-decoration-none text-dark h-100">
            <div class="card-body text-center">
                <i class="bi {{ $r['icon'] }} display-4 text-primary"></i>
                <h6 class="mt-3">{{ $r['title'] }}</h6>
            </div>
        </a>
    </div>
    @endforeach
</div>
@endsection
