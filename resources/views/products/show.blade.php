@extends('layouts.admin')
@section('title', $product->name)
@section('page_title', __('Product') . ' - ' . $product->name)

@section('content')
<div class="row g-3">
    <div class="col-md-4">
        <div class="card shadow-sm">
            @if($product->image)
                <img src="{{ asset('storage/'.$product->image) }}" class="card-img-top" alt="{{ $product->name }}" style="object-fit: cover; max-height: 250px;">
            @else
                <div class="text-center bg-light p-5"><i class="bi bi-box-seam display-1 text-muted"></i></div>
            @endif
            <div class="card-body">
                <h5 class="card-title">{{ $product->name }}</h5>
                <p class="text-muted mb-2">{{ $product->code }}</p>
                <p class="mb-2"><strong>{{ __('Category') }}:</strong> {{ $product->category?->name ?? '-' }}</p>
                <p class="mb-2"><strong>{{ __('Unit') }}:</strong> {{ $product->unit }}</p>
                <p class="mb-2"><strong>{{ __('Price') }}:</strong> {{ number_format((float) $product->price, 2) }}</p>
                <p class="mb-2"><strong>{{ __('Cost') }}:</strong> {{ number_format((float) $product->cost, 2) }}</p>
                <p class="mb-2"><strong>هامش الربح:</strong> {{ $product->profit_margin }}%</p>
                <p class="mb-2"><strong>الحد الأدنى:</strong> {{ $product->min_stock }}</p>
                <p class="mb-2"><strong>{{ __('Status') }}:</strong>
                    @if($product->is_active)
                        <span class="badge bg-success">{{ __('Active') }}</span>
                    @else
                        <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                    @endif
                </p>
                @if($product->description)
                    <hr><p class="text-muted small">{{ $product->description }}</p>
                @endif
                <a href="{{ route('products.index') }}" class="btn btn-secondary btn-sm w-100"><i class="bi bi-arrow-right"></i> العودة</a>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm mb-3">
            <div class="card-header"><h6 class="mb-0">المخزون في كل مستودع</h6></div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead class="table-light"><tr><th>{{ __('Warehouse') }}</th><th class="text-end">الكمية</th><th class="text-end">المحجوز</th><th class="text-end">المتاح</th></tr></thead>
                    <tbody>
                        @forelse($product->stocks()->with('warehouse')->get() as $s)
                            <tr>
                                <td>{{ $s->warehouse?->name }}</td>
                                <td class="text-end">{{ $s->quantity }}</td>
                                <td class="text-end">{{ $s->reserved }}</td>
                                <td class="text-end fw-bold">{{ $s->available }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">لا يوجد مخزون مسجل</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th>الإجمالي</th>
                            <th class="text-end">{{ $product->total_stock }}</th>
                            <th class="text-end">—</th>
                            <th class="text-end">—</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header"><h6 class="mb-0">آخر 20 حركة مخزون</h6></div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead class="table-light"><tr><th>التاريخ</th><th>النوع</th><th>{{ __('Warehouse') }}</th><th class="text-end">الكمية</th><th>قبل</th><th>بعد</th><th>المستخدم</th></tr></thead>
                    <tbody>
                        @forelse($product->stockMovements()->with('warehouse', 'user')->latest()->limit(20)->get() as $m)
                            @php
                                $cls = match ($m->type) { 'in' => 'success', 'out' => 'danger', 'transfer' => 'info', 'adjustment' => 'warning text-dark', default => 'secondary' };
                                $label = match ($m->type) { 'in' => 'وارد', 'out' => 'صادر', 'transfer' => 'تحويل', 'adjustment' => 'جرد', default => $m->type };
                            @endphp
                            <tr>
                                <td>{{ $m->created_at?->format('d/m/Y H:i') }}</td>
                                <td><span class="badge bg-{{ $cls }}">{{ $label }}</span></td>
                                <td>{{ $m->warehouse?->name }}</td>
                                <td class="text-end">{{ $m->quantity }}</td>
                                <td>{{ $m->balance_before }}</td>
                                <td>{{ $m->balance_after }}</td>
                                <td>{{ $m->user?->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted">لا توجد حركات</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
