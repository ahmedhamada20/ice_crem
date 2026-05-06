<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>فاتورة {{ $invoice->invoice_number }}</title>
<style>
body { font-family: 'Cairo', Tahoma; padding: 20px; font-size: 12px; }
table { width: 100%; border-collapse: collapse; }
th, td { border: 1px solid #444; padding: 6px; text-align: right; }
th { background: #eee; }
.totals { width: 40%; margin-right: auto; }
@media print { body { padding: 5mm; } }
</style>
</head>
<body onload="window.print()">
<h2>{{ config('app.name') }}</h2>
<h4>فاتورة بيع</h4>
<p><strong>رقم:</strong> {{ $invoice->invoice_number }} | <strong>التاريخ:</strong> {{ $invoice->issue_date->format('d/m/Y') }}</p>
<p><strong>العميل:</strong> {{ $invoice->customer->name }} - {{ $invoice->customer->phone }}</p>
@if($invoice->order)
<table>
<thead><tr><th>المنتج</th><th>الكمية</th><th>السعر</th><th>الإجمالي</th></tr></thead>
<tbody>
@foreach($invoice->order->items as $item)
<tr><td>{{ $item->product->name }}</td><td>{{ $item->quantity }}</td><td>{{ number_format((float) $item->price, 2) }}</td><td>{{ number_format((float) $item->total, 2) }}</td></tr>
@endforeach
</tbody>
</table>
@endif
<table class="totals">
<tr><th>المجموع</th><td>{{ number_format((float) $invoice->subtotal, 2) }}</td></tr>
<tr><th>الخصم</th><td>{{ number_format((float) $invoice->discount, 2) }}</td></tr>
<tr><th>الضريبة</th><td>{{ number_format((float) $invoice->tax, 2) }}</td></tr>
<tr><th>الإجمالي</th><td><strong>{{ number_format((float) $invoice->total, 2) }}</strong></td></tr>
<tr><th>المدفوع</th><td>{{ number_format((float) $invoice->paid, 2) }}</td></tr>
<tr><th>المتبقي</th><td><strong>{{ number_format((float) $invoice->balance, 2) }}</strong></td></tr>
</table>
</body>
</html>
