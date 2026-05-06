<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Stock;
use App\Models\StockMovement;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $items = $data['items'] ?? [];
            unset($data['items']);

            $order = Order::create($data);

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'price'      => $item['price'],
                    'discount'   => $item['discount'] ?? 0,
                ]);
            }

            $this->calculateTotals($order);

            return $order->fresh('items.product', 'customer');
        });
    }

    public function calculateTotals(Order $order): void
    {
        $subtotal = (float) $order->items()->sum('total');
        $discount = (float) ($order->discount_percent > 0
            ? $subtotal * ($order->discount_percent / 100)
            : $order->discount);
        $afterDiscount = $subtotal - $discount;
        $tax = (float) ($order->tax_percent > 0
            ? $afterDiscount * ($order->tax_percent / 100)
            : $order->tax);

        $order->subtotal  = $subtotal;
        $order->discount  = $discount;
        $order->tax       = $tax;
        $order->total     = $subtotal;
        $order->net_total = $afterDiscount + $tax;
        $order->save();
    }

    public function confirmOrder(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            if ($order->status !== 'pending') {
                throw new Exception('لا يمكن تأكيد طلب ليس في حالة معلق');
            }

            $warehouseId = $order->warehouse_id ?? \App\Models\Warehouse::where('is_main', true)->value('id');

            foreach ($order->items as $item) {
                $stock = Stock::firstOrCreate(
                    ['product_id' => $item->product_id, 'warehouse_id' => $warehouseId],
                    ['quantity' => 0]
                );

                if ($stock->quantity < $item->quantity) {
                    throw new Exception("المخزون غير كافٍ للمنتج: {$item->product->name}");
                }

                $balanceBefore = $stock->quantity;
                $stock->decrement('quantity', $item->quantity);

                StockMovement::create([
                    'product_id'     => $item->product_id,
                    'warehouse_id'   => $warehouseId,
                    'type'           => 'out',
                    'quantity'       => $item->quantity,
                    'balance_before' => $balanceBefore,
                    'balance_after'  => $stock->fresh()->quantity,
                    'reference_type' => Order::class,
                    'reference_id'   => $order->id,
                    'user_id'        => Auth::id(),
                    'notes'          => "خصم من طلب رقم {$order->order_number}",
                ]);
            }

            $order->update([
                'status'       => 'confirmed',
                'confirmed_at' => now(),
                'confirmed_by' => Auth::id(),
                'warehouse_id' => $warehouseId,
            ]);

            // Auto-generate invoice
            $this->generateInvoice($order);

            return $order->fresh();
        });
    }

    public function cancelOrder(Order $order, ?string $reason = null): Order
    {
        return DB::transaction(function () use ($order, $reason) {
            if ($order->status === 'confirmed' && $order->warehouse_id) {
                foreach ($order->items as $item) {
                    $stock = Stock::firstOrCreate(
                        ['product_id' => $item->product_id, 'warehouse_id' => $order->warehouse_id],
                        ['quantity' => 0]
                    );
                    $balanceBefore = $stock->quantity;
                    $stock->increment('quantity', $item->quantity);

                    StockMovement::create([
                        'product_id'     => $item->product_id,
                        'warehouse_id'   => $order->warehouse_id,
                        'type'           => 'in',
                        'quantity'       => $item->quantity,
                        'balance_before' => $balanceBefore,
                        'balance_after'  => $stock->fresh()->quantity,
                        'reference_type' => Order::class,
                        'reference_id'   => $order->id,
                        'user_id'        => Auth::id(),
                        'notes'          => 'إرجاع بسبب إلغاء طلب: '.$reason,
                    ]);
                }
            }

            $order->update([
                'status'       => 'cancelled',
                'cancelled_at' => now(),
                'notes'        => trim(($order->notes ?? '').' '.$reason),
            ]);

            return $order;
        });
    }

    public function generateInvoice(Order $order): Invoice
    {
        return Invoice::create([
            'order_id'    => $order->id,
            'customer_id' => $order->customer_id,
            'issue_date'  => now()->toDateString(),
            'due_date'    => now()->addDays(30)->toDateString(),
            'subtotal'    => $order->subtotal,
            'discount'    => $order->discount,
            'tax'         => $order->tax,
            'total'       => $order->net_total,
            'paid'        => 0,
            'balance'     => $order->net_total,
            'status'      => 'unpaid',
        ]);
    }
}
