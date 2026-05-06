<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class OrdersSeeder extends Seeder
{
    public function run(): void
    {
        // Hard guard: this seeder creates 120 fixture orders + invoices + deliveries.
        if (app()->environment('production') && env('SEED_DEMO') !== '1') {
            $this->command?->error('OrdersSeeder refused to run in production. Set SEED_DEMO=1 to override.');
            return;
        }

        $customers     = Customer::active()->get();
        $products      = Product::active()->get();
        $salesmen      = User::role('salesman')->get();
        $drivers       = User::role('driver')->get();
        $mainWarehouse = Warehouse::where('is_main', true)->first()
                       ?? Warehouse::first();

        if ($customers->isEmpty() || $products->isEmpty() || ! $mainWarehouse) {
            $this->command->warn('OrdersSeeder skipped: missing prerequisites.');
            return;
        }

        $defaultSalesman = $salesmen->first() ?? User::role('admin')->first();

        // Generate ~120 orders spread across the last 90 days
        $orderCount = 120;

        for ($i = 0; $i < $orderCount; $i++) {
            $customer  = $customers->random();
            $salesman  = $salesmen->isNotEmpty() ? $salesmen->random() : $defaultSalesman;
            $orderDate = Carbon::now()->subDays(rand(0, 90))->setTime(rand(8, 18), rand(0, 59));

            DB::transaction(function () use ($customer, $salesman, $orderDate, $products, $mainWarehouse, $drivers) {
                $order = Order::create([
                    'customer_id'      => $customer->id,
                    'salesman_id'      => $salesman?->id,
                    'warehouse_id'     => $mainWarehouse->id,
                    'order_date'       => $orderDate->toDateString(),
                    'delivery_date'    => $orderDate->copy()->addDays(rand(0, 2))->toDateString(),
                    'status'           => 'pending',
                    'discount_percent' => [0, 0, 0, 5, 10][rand(0, 4)],
                    'tax_percent'      => 14,
                    'created_at'       => $orderDate,
                    'updated_at'       => $orderDate,
                ]);

                // 2-6 items per order
                $itemCount = rand(2, 6);
                $picked    = $products->random(min($itemCount, $products->count()));
                $subtotal  = 0;

                foreach ($picked as $product) {
                    $qty   = rand(2, 30);
                    $price = (float) $product->price;
                    $disc  = rand(0, 1) ? 0 : round($price * 0.05, 2);
                    $total = ($qty * $price) - $disc;

                    OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $product->id,
                        'quantity'   => $qty,
                        'price'      => $price,
                        'discount'   => $disc,
                        'total'      => $total,
                        'created_at' => $orderDate,
                        'updated_at' => $orderDate,
                    ]);

                    $subtotal += $total;
                }

                $discount      = $subtotal * ($order->discount_percent / 100);
                $afterDiscount = $subtotal - $discount;
                $tax           = $afterDiscount * ($order->tax_percent / 100);

                $order->update([
                    'subtotal'  => $subtotal,
                    'discount'  => $discount,
                    'tax'       => $tax,
                    'total'     => $subtotal,
                    'net_total' => $afterDiscount + $tax,
                ]);

                // Status distribution
                $rand = rand(1, 100);
                if ($rand <= 10) {
                    // 10% remain pending
                    return;
                }

                if ($rand <= 18) {
                    // 8% cancelled
                    $order->update([
                        'status'       => 'cancelled',
                        'cancelled_at' => $orderDate->copy()->addHours(rand(1, 6)),
                        'notes'        => 'إلغاء بناءً على طلب العميل',
                    ]);
                    return;
                }

                // The remaining 82% — confirm + deduct stock
                $confirmedAt = $orderDate->copy()->addHours(rand(1, 4));
                $this->confirmAndDeduct($order, $mainWarehouse->id, $salesman?->id ?? null, $confirmedAt);

                // Auto-create invoice
                Invoice::create([
                    'order_id'    => $order->id,
                    'customer_id' => $order->customer_id,
                    'issue_date'  => $confirmedAt->toDateString(),
                    'due_date'    => $confirmedAt->copy()->addDays(30)->toDateString(),
                    'subtotal'    => $order->subtotal,
                    'discount'    => $order->discount,
                    'tax'         => $order->tax,
                    'total'       => $order->net_total,
                    'paid'        => 0,
                    'balance'     => $order->net_total,
                    'status'      => 'unpaid',
                    'created_at'  => $confirmedAt,
                    'updated_at'  => $confirmedAt,
                ]);

                // Of the confirmed orders, dispatch ~70%
                if ($drivers->isNotEmpty() && rand(1, 100) <= 70) {
                    $assignedAt = $confirmedAt->copy()->addHours(rand(2, 12));
                    $delivery   = Delivery::create([
                        'order_id'      => $order->id,
                        'driver_id'     => $drivers->random()->id,
                        'vehicle_number'=> 'CAR-' . rand(1000, 9999),
                        'assigned_at'   => $assignedAt,
                        'status'        => 'assigned',
                        'created_at'    => $assignedAt,
                        'updated_at'    => $assignedAt,
                    ]);

                    $order->update(['status' => 'delivering']);

                    // Of those, mark 80% delivered
                    if (rand(1, 100) <= 80) {
                        $startedAt   = $assignedAt->copy()->addMinutes(rand(15, 90));
                        $deliveredAt = $startedAt->copy()->addMinutes(rand(30, 120));
                        $delivery->update([
                            'started_at'   => $startedAt,
                            'delivered_at' => $deliveredAt,
                            'status'       => 'delivered',
                            'start_lat'    => 30.0444 + (rand(-500, 500) / 10000),
                            'start_lng'    => 31.2357 + (rand(-500, 500) / 10000),
                            'end_lat'      => $order->customer->location_lat,
                            'end_lng'      => $order->customer->location_lng,
                        ]);
                        $order->update(['status' => 'delivered']);
                    } elseif (rand(1, 100) <= 30) {
                        $delivery->update([
                            'status'         => 'failed',
                            'failure_reason' => 'العميل غير متواجد',
                        ]);
                    }
                }
            });
        }

        // Recalc customer balances
        $this->recalcCustomerBalances();
    }

    private function confirmAndDeduct(Order $order, int $warehouseId, ?int $userId, Carbon $at): void
    {
        foreach ($order->items as $item) {
            $stock = Stock::where(['product_id' => $item->product_id, 'warehouse_id' => $warehouseId])->lockForUpdate()->first();
            if (! $stock) {
                $stock = Stock::create(['product_id' => $item->product_id, 'warehouse_id' => $warehouseId, 'quantity' => $item->quantity * 5]);
            }

            $before = $stock->quantity;
            // Allow going slightly negative for seed realism if stock insufficient
            $stock->update(['quantity' => max(0, $before - $item->quantity)]);

            StockMovement::create([
                'product_id'     => $item->product_id,
                'warehouse_id'   => $warehouseId,
                'type'           => 'out',
                'quantity'       => $item->quantity,
                'balance_before' => $before,
                'balance_after'  => $stock->fresh()->quantity,
                'reference_type' => Order::class,
                'reference_id'   => $order->id,
                'user_id'        => $userId,
                'notes'          => "خصم من طلب رقم {$order->order_number}",
                'created_at'     => $at,
                'updated_at'     => $at,
            ]);
        }

        $order->update([
            'status'       => 'confirmed',
            'confirmed_at' => $at,
            'confirmed_by' => $userId,
        ]);
    }

    private function recalcCustomerBalances(): void
    {
        Customer::all()->each(function (Customer $c) {
            $invTotal = (float) $c->invoices()->sum('total');
            $payTotal = (float) $c->payments()->sum('amount');
            $c->update(['balance' => $invTotal - $payTotal]);
        });
    }
}
