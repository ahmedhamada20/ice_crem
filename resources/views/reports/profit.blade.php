@extends('layouts.admin')
@section('title', 'تقرير الأرباح')
@section('content')
<div class="card shadow-sm"><div class="card-body">
    @include('reports._filters')
    <table class="table table-bordered table-sm">
        <thead class="table-dark"><tr><th>المنتج</th><th class="text-end">الكمية</th><th class="text-end">الإيراد</th><th class="text-end">التكلفة</th><th class="text-end">الربح</th></tr></thead>
        <tbody>
        @forelse($rows as $r)
            <tr><td>{{ $r['name'] }}</td><td class="text-end">{{ $r['qty'] }}</td>
                <td class="text-end">{{ number_format($r['revenue'], 2) }}</td>
                <td class="text-end">{{ number_format($r['cost'], 2) }}</td>
                <td class="text-end {{ $r['profit'] >= 0 ? 'text-success' : 'text-danger' }} fw-bold">{{ number_format($r['profit'], 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center">لا توجد بيانات</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
@endsection
