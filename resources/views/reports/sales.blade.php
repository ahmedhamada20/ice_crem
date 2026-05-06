@extends('layouts.admin')
@section('title', 'تقرير المبيعات')
@section('page_title', 'تقرير المبيعات')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        @include('reports._filters')
        <table class="table table-bordered table-sm">
            <thead class="table-dark">
                <tr><th>رقم الطلب</th><th>التاريخ</th><th>العميل</th><th>المندوب</th><th class="text-end">المجموع</th><th class="text-end">الخصم</th><th class="text-end">الضريبة</th><th class="text-end">الصافي</th></tr>
            </thead>
            <tbody>
                @forelse($orders as $o)
                <tr>
                    <td>{{ $o->order_number }}</td>
                    <td>{{ $o->order_date->format('d/m/Y') }}</td>
                    <td>{{ $o->customer?->name }}</td>
                    <td>{{ $o->salesman?->name }}</td>
                    <td class="text-end">{{ number_format((float) $o->subtotal, 2) }}</td>
                    <td class="text-end">{{ number_format((float) $o->discount, 2) }}</td>
                    <td class="text-end">{{ number_format((float) $o->tax, 2) }}</td>
                    <td class="text-end">{{ number_format((float) $o->net_total, 2) }}</td>
                </tr>
                @empty
                    <tr><td colspan="8" class="text-center">لا توجد بيانات</td></tr>
                @endforelse
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <th colspan="4">الإجمالي ({{ $totals['count'] }} طلب)</th>
                    <th class="text-end">{{ number_format((float) $totals['subtotal'], 2) }}</th>
                    <th class="text-end">{{ number_format((float) $totals['discount'], 2) }}</th>
                    <th class="text-end">{{ number_format((float) $totals['tax'], 2) }}</th>
                    <th class="text-end">{{ number_format((float) $totals['net'], 2) }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
