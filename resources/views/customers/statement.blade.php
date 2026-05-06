@extends('layouts.admin')
@section('title', 'كشف حساب - ' . $customer->name)
@section('page_title', 'كشف حساب: ' . $customer->name)

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">كشف حساب {{ $customer->name }}</h6>
        <button class="btn btn-primary btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> {{ __('Print') }}</button>
    </div>
    <div class="card-body">
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
            </div>
        </form>

        <table class="table table-bordered table-sm">
            <thead class="table-dark">
                <tr>
                    <th>التاريخ</th>
                    <th>النوع</th>
                    <th>المرجع</th>
                    <th class="text-end">مدين</th>
                    <th class="text-end">دائن</th>
                    <th class="text-end">الرصيد</th>
                    <th>{{ __('Notes') }}</th>
                </tr>
            </thead>
            <tbody>
                @php $totalDebit = 0; $totalCredit = 0; @endphp
                @forelse($statement as $row)
                    @php $totalDebit += $row['debit']; $totalCredit += $row['credit']; @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
                        <td>{{ $row['type'] === 'invoice' ? 'فاتورة' : 'دفعة' }}</td>
                        <td>{{ $row['ref'] }}</td>
                        <td class="text-end">{{ number_format($row['debit'], 2) }}</td>
                        <td class="text-end">{{ number_format($row['credit'], 2) }}</td>
                        <td class="text-end fw-bold">{{ number_format($row['balance'], 2) }}</td>
                        <td>{{ $row['notes'] ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center">{{ __('No data available') }}</td></tr>
                @endforelse
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <th colspan="3" class="text-end">الإجمالي:</th>
                    <th class="text-end">{{ number_format($totalDebit, 2) }}</th>
                    <th class="text-end">{{ number_format($totalCredit, 2) }}</th>
                    <th class="text-end">{{ number_format($totalDebit - $totalCredit, 2) }}</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection

@push('styles')
<style>
@media print {
    .sidebar, .topbar, .btn, form { display: none !important; }
    .card { box-shadow: none !important; }
}
</style>
@endpush
