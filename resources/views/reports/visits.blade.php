@extends('layouts.admin')
@section('title', 'تقرير الزيارات')
@section('content')
<div class="card shadow-sm"><div class="card-body">
    @include('reports._filters')
    <table class="table table-bordered table-sm">
        <thead class="table-dark"><tr><th>التاريخ</th><th>المندوب</th><th>العميل</th><th>دخول</th><th>خروج</th><th>النتيجة</th></tr></thead>
        <tbody>
        @forelse($rows as $v)
            <tr><td>{{ $v->visit_date->format('d/m/Y') }}</td><td>{{ $v->salesman?->name }}</td><td>{{ $v->customer?->name }}</td>
                <td>{{ $v->check_in?->format('H:i') }}</td><td>{{ $v->check_out?->format('H:i') }}</td><td>{{ $v->result }}</td></tr>
        @empty
            <tr><td colspan="6" class="text-center">لا توجد بيانات</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
@endsection
