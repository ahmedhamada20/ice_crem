@extends('layouts.admin')
@section('title', $user->name)
@section('page_title', __('User') . ' - ' . $user->name)

@section('content')
<div class="row g-3">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                @if($user->avatar)
                    <img src="{{ asset('storage/'.$user->avatar) }}" class="rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                @else
                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 120px; height: 120px;">
                        <i class="bi bi-person-circle display-3 text-muted"></i>
                    </div>
                @endif

                <h5>{{ $user->name }}</h5>
                <p class="text-muted">{{ $user->email }}</p>

                <div class="mb-2">
                    @foreach($user->roles as $role)
                        <span class="badge bg-primary">{{ $role->name }}</span>
                    @endforeach
                </div>

                @if($user->status === 'active')
                    <span class="badge bg-success">{{ __('Active') }}</span>
                @else
                    <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                @endif

                <hr>
                <ul class="list-unstyled text-start mb-0">
                    <li class="mb-2"><i class="bi bi-telephone"></i> {{ $user->phone ?? 'لا يوجد' }}</li>
                    <li class="mb-2"><i class="bi bi-map"></i> {{ $user->zone?->name ?? 'بدون منطقة' }}</li>
                    <li class="mb-2"><i class="bi bi-calendar"></i> انضم في {{ $user->created_at?->format('d/m/Y') }}</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="card shadow-sm text-bg-primary">
                    <div class="card-body">
                        <small>طلبات (شهر)</small>
                        <h4>{{ $user->orders()->whereDate('order_date', '>=', now()->startOfMonth())->count() }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm text-bg-success">
                    <div class="card-body">
                        <small>توصيلات (شهر)</small>
                        <h4>{{ $user->deliveries()->whereDate('assigned_at', '>=', now()->startOfMonth())->count() }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm text-bg-info">
                    <div class="card-body">
                        <small>زيارات (شهر)</small>
                        <h4>{{ $user->visits()->whereDate('visit_date', '>=', now()->startOfMonth())->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header"><h6 class="mb-0">آخر النشاط</h6></div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead class="table-light"><tr><th>التاريخ</th><th>النوع</th><th>المرجع</th></tr></thead>
                    <tbody>
                    @php
                        $orders = $user->orders()->latest()->limit(5)->get()->map(fn($o) => ['date' => $o->created_at, 'type' => 'طلب', 'ref' => $o->order_number]);
                        $deliveries = $user->deliveries()->latest()->limit(5)->get()->map(fn($d) => ['date' => $d->assigned_at ?? $d->created_at, 'type' => 'توصيلة', 'ref' => $d->delivery_number]);
                        $activity = $orders->merge($deliveries)->sortByDesc('date')->take(10);
                    @endphp
                    @forelse($activity as $a)
                        <tr><td>{{ $a['date']?->format('d/m/Y H:i') }}</td><td>{{ $a['type'] }}</td><td>{{ $a['ref'] }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted">لا يوجد نشاط</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
