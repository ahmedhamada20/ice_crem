@extends('layouts.admin')
@section('title', 'تقرير التوصيل')
@section('content')
<div class="card shadow-sm"><div class="card-body">
    @include('reports._filters')
    <table class="table table-bordered table-sm">
        <thead class="table-dark"><tr><th>السائق</th><th class="text-end">الإجمالي</th><th class="text-end">تم</th><th class="text-end">فشل</th><th class="text-end">نسبة النجاح</th></tr></thead>
        <tbody>
        @forelse($rows as $r)
            @php $success = $r->total > 0 ? round($r->delivered / $r->total * 100, 1) : 0; @endphp
            <tr><td>{{ $r->name }}</td><td class="text-end">{{ $r->total }}</td>
                <td class="text-end text-success">{{ $r->delivered }}</td>
                <td class="text-end text-danger">{{ $r->failed }}</td>
                <td class="text-end fw-bold">{{ $success }}%</td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center">لا توجد بيانات</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
@endsection
