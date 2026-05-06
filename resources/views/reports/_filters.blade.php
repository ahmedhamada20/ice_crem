@php $from = $from ?? now()->startOfMonth()->toDateString(); $to = $to ?? now()->toDateString(); @endphp
<form method="GET" class="row g-2 mb-3">
    <div class="col-md-3">
        <label>{{ __('From Date') }}</label>
        <input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}">
    </div>
    <div class="col-md-3">
        <label>{{ __('To Date') }}</label>
        <input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}">
    </div>
    <div class="col-md-3 align-self-end">
        <button class="btn btn-primary btn-sm">{{ __('Filter') }}</button>
        <button type="button" class="btn btn-secondary btn-sm" onclick="window.print()"><i class="bi bi-printer"></i></button>
    </div>
</form>
