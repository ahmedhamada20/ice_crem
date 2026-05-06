@extends('layouts.admin')
@section('title', 'المنتجات الأكثر مبيعاً')
@section('page_title', 'المنتجات الأكثر مبيعاً')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        @include('reports._filters')
        <table class="table table-bordered table-sm">
            <thead class="table-dark"><tr><th>الكود</th><th>المنتج</th><th class="text-end">الكمية</th><th class="text-end">الإجمالي</th></tr></thead>
            <tbody>
                @forelse($rows as $r)
                    <tr><td>{{ $r->code }}</td><td>{{ $r->name }}</td><td class="text-end">{{ $r->qty }}</td><td class="text-end">{{ number_format($r->total, 2) }}</td></tr>
                @empty
                    <tr><td colspan="4" class="text-center">لا توجد بيانات</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
