<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\StockMovement;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function addStock(int $productId, int $warehouseId, int $qty, ?string $notes = null): Stock
    {
        return DB::transaction(function () use ($productId, $warehouseId, $qty, $notes) {
            $stock = Stock::firstOrCreate(
                ['product_id' => $productId, 'warehouse_id' => $warehouseId],
                ['quantity' => 0]
            );

            $before = $stock->quantity;
            $stock->increment('quantity', $qty);

            StockMovement::create([
                'product_id'     => $productId,
                'warehouse_id'   => $warehouseId,
                'type'           => 'in',
                'quantity'       => $qty,
                'balance_before' => $before,
                'balance_after'  => $stock->fresh()->quantity,
                'user_id'        => Auth::id(),
                'notes'          => $notes,
            ]);

            return $stock->fresh();
        });
    }

    public function reduceStock(int $productId, int $warehouseId, int $qty, ?string $notes = null): Stock
    {
        return DB::transaction(function () use ($productId, $warehouseId, $qty, $notes) {
            $stock = Stock::where(['product_id' => $productId, 'warehouse_id' => $warehouseId])->lockForUpdate()->first();
            if (! $stock || $stock->quantity < $qty) {
                throw new Exception('المخزون غير كافٍ');
            }

            $before = $stock->quantity;
            $stock->decrement('quantity', $qty);

            StockMovement::create([
                'product_id'     => $productId,
                'warehouse_id'   => $warehouseId,
                'type'           => 'out',
                'quantity'       => $qty,
                'balance_before' => $before,
                'balance_after'  => $stock->fresh()->quantity,
                'user_id'        => Auth::id(),
                'notes'          => $notes,
            ]);

            return $stock->fresh();
        });
    }

    public function transferStock(int $productId, int $fromWarehouse, int $toWarehouse, int $qty, ?string $notes = null): array
    {
        return DB::transaction(function () use ($productId, $fromWarehouse, $toWarehouse, $qty, $notes) {
            $from = $this->reduceStock($productId, $fromWarehouse, $qty, "تحويل: $notes");
            $to   = $this->addStock($productId, $toWarehouse, $qty, "تحويل وارد: $notes");

            // Mark as transfer
            StockMovement::where('product_id', $productId)
                ->where('warehouse_id', $fromWarehouse)
                ->latest('id')->first()
                ?->update(['type' => 'transfer', 'to_warehouse_id' => $toWarehouse]);

            return ['from' => $from, 'to' => $to];
        });
    }

    public function adjustStock(int $productId, int $warehouseId, int $newQuantity, ?string $notes = null): Stock
    {
        return DB::transaction(function () use ($productId, $warehouseId, $newQuantity, $notes) {
            $stock = Stock::firstOrCreate(
                ['product_id' => $productId, 'warehouse_id' => $warehouseId],
                ['quantity' => 0]
            );

            $before = $stock->quantity;
            $diff = $newQuantity - $before;
            $stock->update(['quantity' => $newQuantity]);

            StockMovement::create([
                'product_id'     => $productId,
                'warehouse_id'   => $warehouseId,
                'type'           => 'adjustment',
                'quantity'       => abs($diff),
                'balance_before' => $before,
                'balance_after'  => $newQuantity,
                'user_id'        => Auth::id(),
                'notes'          => $notes ?? 'جرد',
            ]);

            return $stock->fresh();
        });
    }
}
