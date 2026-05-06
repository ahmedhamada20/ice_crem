<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Warehouse;
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

    /**
     * Confirm a pending order — does NOT touch stock anymore.
     * Stock is only deducted at delivery time (see markDelivered).
     */
    public function confirmOrder(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            if ($order->status !== 'pending') {
                throw new Exception('لا يمكن تأكيد طلب ليس في حالة معلق');
            }

            $warehouseId = $order->warehouse_id ?? Warehouse::where('is_main', true)->value('id');

            $order->update([
                'status'       => 'confirmed',
                'confirmed_at' => now(),
                'confirmed_by' => Auth::id(),
                'warehouse_id' => $warehouseId,
            ]);

            $this->generateInvoice($order);

            return $order->fresh();
        });
    }

    /**
     * Mark order as delivered AND deduct stock.
     * Called by DeliveryService when the driver completes a delivery.
     * Idempotent: if stock was already deducted (legacy data), skip the deduction.
     */
    public function markDelivered(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            if (! in_array($order->status, ['confirmed', 'delivering'])) {
                throw new Exception('الطلب يجب أن يكون مؤكداً أو قيد التوصيل قبل التسليم');
            }

            $warehouseId = $order->warehouse_id
                ?? Warehouse::where('is_main', true)->value('id');

            // Idempotency guard — if 'out' movements for this order already exist, skip
            $alreadyDeducted = StockMovement::where('reference_type', Order::class)
                ->where('reference_id', $order->id)
                ->where('type', 'out')
                ->exists();

            if (! $alreadyDeducted) {
                foreach ($order->items as $item) {
                    $stock = Stock::firstOrCreate(
                        ['product_id' => $item->product_id, 'warehouse_id' => $warehouseId],
                        ['quantity' => 0]
                    );

                    if ($stock->quantity < $item->quantity) {
                        // Allow it but log; the warehouse keeper can fix later via adjustment
                        \Log::warning("Stock went negative for product {$item->product_id} in warehouse $warehouseId due to order {$order->id}");
                    }

                    $before = $stock->quantity;
                    $stock->decrement('quantity', $item->quantity);

                    StockMovement::create([
                        'product_id'     => $item->product_id,
                        'warehouse_id'   => $warehouseId,
                        'type'           => 'out',
                        'quantity'       => $item->quantity,
                        'balance_before' => $before,
                        'balance_after'  => $stock->fresh()->quantity,
                        'reference_type' => Order::class,
                        'reference_id'   => $order->id,
                        'user_id'        => Auth::id(),
                        'notes'          => "تسليم طلب رقم {$order->order_number}",
                    ]);
                }
            }

            $order->update([
                'status'       => 'delivered',
                'warehouse_id' => $warehouseId,
            ]);

            return $order->fresh();
        });
    }

    /**
     * Mark a delivered order as returned — restores stock.
     */
    public function returnOrder(Order $order, ?string $reason = null): Order
    {
        return DB::transaction(function () use ($order, $reason) {
            if ($order->status !== 'delivered') {
                throw new Exception('فقط الطلبات التي تم تسليمها يمكن إرجاعها');
            }

            $warehouseId = $order->warehouse_id ?? Warehouse::where('is_main', true)->value('id');

            // Idempotency: skip if return movements already recorded
            $alreadyReturned = StockMovement::where('reference_type', Order::class)
                ->where('reference_id', $order->id)
                ->where('type', 'in')
                ->where('notes', 'like', '%إرجاع%')
                ->exists();

            if (! $alreadyReturned) {
                foreach ($order->items as $item) {
                    $stock = Stock::firstOrCreate(
                        ['product_id' => $item->product_id, 'warehouse_id' => $warehouseId],
                        ['quantity' => 0]
                    );

                    $before = $stock->quantity;
                    $stock->increment('quantity', $item->quantity);

                    StockMovement::create([
                        'product_id'     => $item->product_id,
                        'warehouse_id'   => $warehouseId,
                        'type'           => 'in',
                        'quantity'       => $item->quantity,
                        'balance_before' => $before,
                        'balance_after'  => $stock->fresh()->quantity,
                        'reference_type' => Order::class,
                        'reference_id'   => $order->id,
                        'user_id'        => Auth::id(),
                        'notes'          => 'إرجاع طلب رقم ' . $order->order_number . ($reason ? " — $reason" : ''),
                    ]);
                }
            }

            $appendNote = '[مرتجع' . ($reason ? ": $reason" : '') . ']';
            $order->update([
                'status' => 'returned',
                'notes'  => trim(($order->notes ?? '') . ' ' . $appendNote),
            ]);

            return $order->fresh();
        });
    }

    /**
     * Cancel an order. Only restores stock if it was already delivered (deducted).
     */
    public function cancelOrder(Order $order, ?string $reason = null): Order
    {
        return DB::transaction(function () use ($order, $reason) {
            if (in_array($order->status, ['cancelled', 'returned'])) {
                throw new Exception('الطلب ملغي أو مرتجع بالفعل');
            }

            // Restore stock only if it had been deducted (i.e. delivered)
            $hasOutMovements = StockMovement::where('reference_type', Order::class)
                ->where('reference_id', $order->id)
                ->where('type', 'out')
                ->exists();

            if ($hasOutMovements && $order->warehouse_id) {
                foreach ($order->items as $item) {
                    $stock = Stock::firstOrCreate(
                        ['product_id' => $item->product_id, 'warehouse_id' => $order->warehouse_id],
                        ['quantity' => 0]
                    );
                    $before = $stock->quantity;
                    $stock->increment('quantity', $item->quantity);

                    StockMovement::create([
                        'product_id'     => $item->product_id,
                        'warehouse_id'   => $order->warehouse_id,
                        'type'           => 'in',
                        'quantity'       => $item->quantity,
                        'balance_before' => $before,
                        'balance_after'  => $stock->fresh()->quantity,
                        'reference_type' => Order::class,
                        'reference_id'   => $order->id,
                        'user_id'        => Auth::id(),
                        'notes'          => 'إرجاع بسبب إلغاء طلب ' . ($reason ? "($reason)" : ''),
                    ]);
                }
            }

            $order->update([
                'status'       => 'cancelled',
                'cancelled_at' => now(),
                'notes'        => trim(($order->notes ?? '') . ' ' . ($reason ? "[ملغي: $reason]" : '[ملغي]')),
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
