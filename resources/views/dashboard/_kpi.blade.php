@php
    $color = $color ?? 'primary';
    $col   = $col ?? 3;
    $icon  = $icon ?? 'graph-up';
    $sub   = $sub ?? null;
@endphp
<div class="col-md-{{ $col }}">
    <div class="card kpi-card text-bg-{{ $color }} shadow-sm h-100">
        <div class="card-body">
            <small class="d-block">{{ $label }}</small>
            <h3 class="mb-0">{{ $value }}</h3>
            @if($sub)
                <small><i class="bi bi-{{ $icon }}"></i> {{ $sub }}</small>
            @endif
        </div>
    </div>
</div>
