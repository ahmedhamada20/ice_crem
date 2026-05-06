@extends('layouts.admin')
@section('title', 'تقرير العملاء')
@section('page_title', 'تقرير العملاء')
@section('content')
<div class="card shadow-sm"><div class="card-body">
    @include('reports._filters')
    <table class="table table-bordered table-sm">
        <thead class="table-dark"><tr><th>الكود</th><th>الاسم</th><th>الهاتف</th><th class="text-end">عدد الطلبات</th><th class="text-end">الإجمالي</th></tr></thead>
        <tbody>
        @forelse($rows as $r)
            <tr><td>{{ $r->code }}</td><td>{{ $r->name }}</td><td>{{ $r->phone }}</td><td class="text-end">{{ $r->orders_count }}</td><td class="text-end">{{ number_format($r->total, 2) }}</td></tr>
        @empty
            <tr><td colspan="5" class="text-center">لا توجد بيانات</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
@endsection
