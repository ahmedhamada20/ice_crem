@extends('layouts.admin')
@section('title', $zone->name)
@section('page_title', __('Zone') . ' - ' . $zone->name)

@section('content')
<div class="row g-3">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5>{{ $zone->name }}</h5>
                <p class="text-muted">{{ $zone->code }}</p>
                <hr>
                <p><strong>المدير:</strong> {{ $zone->manager?->name ?? 'لا يوجد' }}</p>
                <p><strong>{{ __('Status') }}:</strong>
                    @if($zone->is_active)<span class="badge bg-success">{{ __('Active') }}</span>
                    @else<span class="badge bg-secondary">{{ __('Inactive') }}</span>@endif
                </p>
                @if($zone->notes)<hr><p class="text-muted">{{ $zone->notes }}</p>@endif

                <div class="row text-center mt-3">
                    <div class="col"><h4>{{ $zone->customers()->count() }}</h4><small class="text-muted">عملاء</small></div>
                    <div class="col"><h4>{{ $zone->users()->count() }}</h4><small class="text-muted">مستخدمين</small></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header"><h6 class="mb-0">العملاء في هذه المنطقة</h6></div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead class="table-light"><tr><th>الكود</th><th>الاسم</th><th>الهاتف</th><th>{{ __('Type') }}</th><th class="text-end">الرصيد</th></tr></thead>
                    <tbody>
                    @forelse($zone->customers()->latest()->limit(20)->get() as $c)
                        <tr><td>{{ $c->code }}</td><td>{{ $c->name }}</td><td>{{ $c->phone }}</td><td>{{ __($c->type) }}</td><td class="text-end">{{ number_format((float) $c->balance, 2) }}</td></tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">لا يوجد عملاء</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
