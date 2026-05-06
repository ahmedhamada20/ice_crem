@extends('layouts.admin')
@section('title', $payment->payment_number)
@section('page_title', 'تفاصيل الدفعة')

@section('content')
<div class="card shadow-sm" style="max-width: 700px; margin: 0 auto;">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">{{ $payment->payment_number }}</h6>
        <button class="btn btn-sm btn-secondary" onclick="window.print()"><i class="bi bi-printer"></i> {{ __('Print') }}</button>
    </div>
    <div class="card-body">
        <table class="table">
            <tr><th width="40%">{{ __('Customer') }}</th><td><a href="{{ route('customers.show', $payment->customer) }}">{{ $payment->customer?->name }}</a></td></tr>
            <tr><th>{{ __('Invoice') }}</th><td>
                @if($payment->invoice)<a href="{{ route('invoices.show', $payment->invoice) }}">{{ $payment->invoice->invoice_number }}</a>
                @else دفعة بدون ربط بفاتورة @endif
            </td></tr>
            <tr><th>التاريخ</th><td>{{ $payment->payment_date?->format('d/m/Y') }}</td></tr>
            <tr><th>المبلغ</th><td class="fw-bold fs-5">{{ number_format((float) $payment->amount, 2) }}</td></tr>
            <tr><th>الطريقة</th><td>{{ $payment->method_label }}</td></tr>
            <tr><th>المرجع</th><td>{{ $payment->reference ?? '-' }}</td></tr>
            <tr><th>المُحصّل</th><td>{{ $payment->user?->name ?? '-' }}</td></tr>
            <tr><th>{{ __('Notes') }}</th><td>{{ $payment->notes ?? '-' }}</td></tr>
        </table>
    </div>
</div>
@endsection

@push('styles')
<style>
@media print { .sidebar, .topbar, .btn, .card-header { display: none !important; } .card { box-shadow: none !important; border: none !important; } }
</style>
@endpush
