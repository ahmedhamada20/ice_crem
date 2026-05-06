<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>طلب - {{ $order->order_number }}</title>
    <style>
        body { font-family: 'Cairo', Tahoma, sans-serif; font-size: 12px; padding: 20px; }
        h2 { margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: right; }
        th { background: #eee; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .totals { width: 40%; margin-right: auto; margin-top: 10px; }
        @media print { body { padding: 5mm; } }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <div>
            <h2>{{ config('app.name') }}</h2>
            <p>طلب بيع</p>
        </div>
        <div>
            <p><strong>رقم الطلب:</strong> {{ $order->order_number }}</p>
            <p><strong>التاريخ:</strong> {{ $order->order_date->format('d/m/Y') }}</p>
        </div>
    </div>

    <div>
        <p><strong>العميل:</strong> {{ $order->customer->name }} - {{ $order->customer->phone }}</p>
        <p><strong>العنوان:</strong> {{ $order->customer->address }}</p>
    </div>

    <table>
        <thead>
            <tr><th>#</th><th>المنتج</th><th>الكمية</th><th>السعر</th><th>الخصم</th><th>الإجمالي</th></tr>
        </thead>
        <tbody>
        @foreach($order->items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format((float) $item->price, 2) }}</td>
                <td>{{ number_format((float) $item->discount, 2) }}</td>
                <td>{{ number_format((float) $item->total, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><th>المجموع</th><td>{{ number_format((float) $order->subtotal, 2) }}</td></tr>
        <tr><th>الخصم</th><td>{{ number_format((float) $order->discount, 2) }}</td></tr>
        <tr><th>الضريبة</th><td>{{ number_format((float) $order->tax, 2) }}</td></tr>
        <tr><th>الإجمالي الصافي</th><td><strong>{{ number_format((float) $order->net_total, 2) }}</strong></td></tr>
    </table>

    @if($order->notes)
        <p style="margin-top: 20px;"><strong>ملاحظات:</strong> {{ $order->notes }}</p>
    @endif

    <div style="margin-top: 40px; display: flex; justify-content: space-between;">
        <div>توقيع المندوب: ___________</div>
        <div>توقيع العميل: ___________</div>
    </div>
</body>
</html>
