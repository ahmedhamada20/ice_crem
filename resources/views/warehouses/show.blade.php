@extends('layouts.admin')
@section('title', $warehouse->name)
@section('page_title', __('Warehouse') . ' - ' . $warehouse->name)

@section('content')
<div class="row g-3">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5>{{ $warehouse->name }} @if($warehouse->is_main)<span class="badge bg-warning text-dark">رئيسي</span>@endif</h5>
                <p class="text-muted">{{ $warehouse->code }}</p>
                <hr>
                <p><strong>المدير:</strong> {{ $warehouse->manager?->name ?? '-' }}</p>
                <p><strong>{{ __('Phone') }}:</strong> {{ $warehouse->phone ?? '-' }}</p>
                <p><strong>{{ __('Address') }}:</strong> {{ $warehouse->address ?? '-' }}</p>
                <p><strong>{{ __('Status') }}:</strong>
                    @if($warehouse->is_active)<span class="badge bg-success">{{ __('Active') }}</span>
                    @else<span class="badge bg-secondary">{{ __('Inactive') }}</span>@endif
                </p>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        @php
            $stocks = $warehouse->stocks()->with('product')->get();
            $total = $stocks->sum('quantity');
            $lowStock = $stocks->filter(fn ($s) => $s->product && $s->product->min_stock > 0 && $s->quantity <= $s->product->min_stock)->count();
        @endphp

        <div class="row g-3 mb-3">
            <div class="col-md-4"><div class="card shadow-sm text-bg-primary"><div class="card-body"><small>إجمالي عدد الأصناف</small><h4>{{ $stocks->count() }}</h4></div></div></div>
            <div class="col-md-4"><div class="card shadow-sm text-bg-success"><div class="card-body"><small>إجمالي الكمية</small><h4>{{ $total }}</h4></div></div></div>
            <div class="col-md-4"><div class="card shadow-sm text-bg-warning"><div class="card-body"><small>أصناف مخزون منخفض</small><h4>{{ $lowStock }}</h4></div></div></div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header"><h6 class="mb-0">المخزون</h6></div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead class="table-light"><tr><th>{{ __('Code') }}</th><th>{{ __('Product') }}</th><th class="text-end">الكمية</th><th class="text-end">الحد الأدنى</th><th>{{ __('Status') }}</th></tr></thead>
                    <tbody>
                    @forelse($stocks as $s)
                        @php
                            $min = $s->product?->min_stock ?? 0;
                            [$st, $cls] = $s->quantity <= 0 ? ['نافد','danger'] : (($min > 0 && $s->quantity <= $min) ? ['منخفض','warning text-dark'] : ['متاح','success']);
                        @endphp
                        <tr><td>{{ $s->product?->code }}</td><td>{{ $s->product?->name }}</td><td class="text-end">{{ $s->quantity }}</td><td class="text-end">{{ $min }}</td><td><span class="badge bg-{{ $cls }}">{{ $st }}</span></td></tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">لا يوجد مخزون</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
