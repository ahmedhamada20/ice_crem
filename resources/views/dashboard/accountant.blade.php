@extends('layouts.admin')
@section('title', __('Dashboard'))
@section('page_title', 'لوحة المحاسبة')

@section('content')
<div class="alert alert-warning py-2 mb-3">
    <i class="bi bi-cash-coin"></i> لوحة الفواتير والتحصيل
    <a href="{{ route('invoices.create') }}" class="btn btn-sm btn-warning ms-3">
        <i class="bi bi-plus"></i> فاتورة جديدة
    </a>
    <a href="{{ route('payments.index') }}" class="btn btn-sm btn-outline-dark">
        <i class="bi bi-cash"></i> تسجيل دفعة
    </a>
</div>

<div class="row g-3 mb-4">
    @include('dashboard._kpi', ['label' => 'تحصيل اليوم',     'value' => number_format($kpis['collected_today'], 0), 'color' => 'success', 'icon' => 'cash',     'sub' => 'ج.م'])
    @include('dashboard._kpi', ['label' => 'تحصيل الشهر',     'value' => number_format($kpis['collected_month'], 0), 'color' => 'primary', 'icon' => 'wallet',   'sub' => 'ج.م'])
    @include('dashboard._kpi', ['label' => 'فواتير غير مدفوعة','value' => $kpis['unpaid_invoices'],                    'color' => 'warning', 'icon' => 'receipt',  'sub' => 'فاتورة'])
    @include('dashboard._kpi', ['label' => 'متأخرات',         'value' => $kpis['overdue_count'],                      'color' => 'danger',  'icon' => 'exclamation-triangle', 'sub' => 'فاتورة'])
</div>

<div class="row g-3 mb-4">
    @include('dashboard._kpi', ['label' => 'إجمالي المستحقات',  'value' => number_format($kpis['outstanding_total'], 0), 'color' => 'light text-dark', 'col' => 6, 'icon' => 'cash-stack', 'sub' => 'ج.م'])
    @include('dashboard._kpi', ['label' => 'إجمالي المتأخرات',  'value' => number_format($kpis['overdue_total'], 0),     'color' => 'danger',           'col' => 6, 'icon' => 'exclamation-circle', 'sub' => 'ج.م'])
</div>

<div class="row g-3 mb-4">
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header"><h6 class="mb-0">المتحصلات - آخر 30 يوم</h6></div>
            <div class="card-body"><canvas id="collectedChart"></canvas></div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-header"><h6 class="mb-0">الذمم المتأخرة (Aging)</h6></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                    @foreach($buckets as $bucket => $total)
                        @php
                            $cls = match($bucket) { '0-30' => 'success', '31-60' => 'warning text-dark', '61-90' => 'danger', '90+' => 'dark', default => 'secondary' };
                        @endphp
                        <tr>
                            <td><span class="badge bg-{{ $cls }}">{{ $bucket }} يوم</span></td>
                            <td class="text-end fw-bold">{{ number_format($total, 2) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <a href="{{ route('reports.aging') }}" class="btn btn-sm btn-link p-0">عرض التقرير الكامل</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header"><h6 class="mb-0">أكبر 8 مدينين</h6></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>الكود</th><th>العميل</th><th>الهاتف</th><th class="text-end">الرصيد</th></tr></thead>
                    <tbody>
                    @forelse($topDebtors as $c)
                        <tr style="cursor:pointer" onclick="location.href='{{ route('customers.show', $c) }}'">
                            <td>{{ $c->code }}</td>
                            <td>{{ $c->name }}</td>
                            <td>{{ $c->phone }}</td>
                            <td class="text-end fw-bold text-danger">{{ number_format((float) $c->balance, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">لا يوجد مدينون</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header"><h6 class="mb-0">آخر المدفوعات</h6></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>التاريخ</th><th>العميل</th><th>الطريقة</th><th class="text-end">المبلغ</th></tr></thead>
                    <tbody>
                    @forelse($recentPayments as $p)
                        <tr>
                            <td>{{ $p->payment_date?->format('d/m/Y') }}</td>
                            <td>{{ $p->customer?->name }}</td>
                            <td><small>{{ $p->method_label }}</small></td>
                            <td class="text-end fw-bold text-success">{{ number_format((float) $p->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">لا توجد مدفوعات حديثة</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
new Chart(document.getElementById('collectedChart'), {
    type: 'bar',
    data: {
        labels: @json($collected30->pluck('date')),
        datasets: [{ label: 'متحصل', data: @json($collected30->pluck('total')), backgroundColor: '#198754' }]
    }
});
</script>
@endpush
