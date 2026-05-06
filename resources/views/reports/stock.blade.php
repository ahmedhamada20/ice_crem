@extends('layouts.admin')
@section('title', 'تقرير المخزون')
@section('content')
<div class="card shadow-sm"><div class="card-body">
    <table class="table table-bordered table-sm">
        <thead class="table-dark"><tr><th>الكود</th><th>المنتج</th><th>المستودع</th><th class="text-end">الكمية</th><th class="text-end">المحجوز</th><th class="text-end">المتاح</th><th>الحالة</th></tr></thead>
        <tbody>
        @forelse($rows as $r)
            @php
                $min = $r->product?->min_stock ?? 0;
                $st = $r->quantity <= 0 ? 'نافد' : (($min > 0 && $r->quantity <= $min) ? 'منخفض' : 'متاح');
                $cls = $r->quantity <= 0 ? 'danger' : (($min > 0 && $r->quantity <= $min) ? 'warning text-dark' : 'success');
            @endphp
            <tr><td>{{ $r->product?->code }}</td><td>{{ $r->product?->name }}</td><td>{{ $r->warehouse?->name }}</td>
                <td class="text-end">{{ $r->quantity }}</td><td class="text-end">{{ $r->reserved }}</td><td class="text-end">{{ $r->available }}</td>
                <td><span class="badge bg-{{ $cls }}">{{ $st }}</span></td></tr>
        @empty
            <tr><td colspan="7" class="text-center">لا توجد بيانات</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
@endsection
