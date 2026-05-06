@extends('layouts.admin')
@section('title', 'الذمم المتأخرة')
@section('page_title', 'الذمم المتأخرة (Aging)')
@section('content')
<div class="row g-3 mb-3">
    @foreach($buckets as $bucket => $total)
    <div class="col-md-3"><div class="card shadow-sm"><div class="card-body">
        <small>{{ $bucket }} يوم</small>
        <h5 class="mb-0">{{ number_format($total, 2) }}</h5>
    </div></div></div>
    @endforeach
</div>

<div class="card shadow-sm"><div class="card-body">
    <table class="table table-bordered table-sm">
        <thead class="table-dark"><tr><th>العميل</th><th class="text-end">0-30</th><th class="text-end">31-60</th><th class="text-end">61-90</th><th class="text-end">90+</th><th class="text-end">الإجمالي</th></tr></thead>
        <tbody>
        @forelse($byCustomer as $c)
            <tr><td>{{ $c['name'] }}</td>
                <td class="text-end">{{ number_format($c['0-30'], 2) }}</td>
                <td class="text-end">{{ number_format($c['31-60'], 2) }}</td>
                <td class="text-end">{{ number_format($c['61-90'], 2) }}</td>
                <td class="text-end text-danger">{{ number_format($c['90+'], 2) }}</td>
                <td class="text-end fw-bold">{{ number_format($c['total'], 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center">لا توجد بيانات</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
@endsection
