<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class StockSeeder extends Seeder
{
    public function run(): void
    {
        // Hard guard: seeds opening stock balances; should not auto-run in production.
        if (app()->environment('production') && env('SEED_DEMO') !== '1') {
            $this->command?->error('StockSeeder refused to run in production. Set SEED_DEMO=1 to override.');
            return;
        }

        $warehouses = Warehouse::all();
        $products   = Product::active()->get();
        $adminId    = User::role('warehouse-keeper')->value('id') ?? User::role('admin')->value('id');

        foreach ($warehouses as $warehouse) {
            foreach ($products as $product) {
                // Main warehouse gets bigger stock; branches get less
                $base = $warehouse->is_main ? rand(150, 500) : rand(50, 200);

                // Some products always have low stock for realism
                if (in_array($product->code, ['P-F006', 'P-B003', 'P-B004'])) {
                    $base = rand(0, max(1, (int) ($product->min_stock * 0.6)));
                }

                Stock::updateOrCreate(
                    ['product_id' => $product->id, 'warehouse_id' => $warehouse->id],
                    ['quantity' => $base, 'reserved' => 0]
                );

                if ($base > 0) {
                    StockMovement::create([
                        'product_id'     => $product->id,
                        'warehouse_id'   => $warehouse->id,
                        'type'           => 'in',
                        'quantity'       => $base,
                        'balance_before' => 0,
                        'balance_after'  => $base,
                        'user_id'        => $adminId,
                        'notes'          => 'رصيد افتتاحي',
                        'created_at'     => now()->subDays(rand(30, 90)),
                        'updated_at'     => now()->subDays(rand(30, 90)),
                    ]);
                }
            }
        }
    }
}
