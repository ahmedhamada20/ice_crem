<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    protected $table = 'stock';

    protected $fillable = ['product_id', 'warehouse_id', 'quantity', 'reserved'];

    protected $casts = [
        'quantity' => 'integer',
        'reserved' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function getAvailableAttribute(): int
    {
        return (int) ($this->quantity - $this->reserved);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', \DB::raw('(select min_stock from products where products.id = stock.product_id)'));
    }
}
