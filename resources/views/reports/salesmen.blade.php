@extends('layouts.admin')
@section('title', 'أداء المناديب')
@section('page_title', 'أداء المناديب')
@section('content')
<div class="card shadow-sm"><div class="card-body">
    @include('reports._filters')
    <table class="table table-bordered table-sm">
        <thead class="table-dark"><tr><th>المندوب</th><th class="text-end">الطلبات</th><th class="text-end">الإجمالي</th><th class="text-end">الزيارات</th></tr></thead>
        <tbody>
        @forelse($rows as $r)
            <tr><td>{{ $r->name }}</td><td class="text-end">{{ $r->orders_count }}</td><td class="text-end">{{ number_format($r->orders_total, 2) }}</td><td class="text-end">{{ $r->visits_count }}</td></tr>
        @empty
            <tr><td colspan="4" class="text-center">لا توجد بيانات</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
@endsection
