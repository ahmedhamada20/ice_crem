<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property int|null $category_id
 * @property string $unit
 * @property float $price
 * @property float $cost
 * @property int $min_stock
 * @property string|null $image
 * @property bool $is_active
 */
class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'name', 'category_id', 'unit', 'price', 'cost',
        'min_stock', 'image', 'description', 'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'min_stock' => 'integer',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getTotalStockAttribute(): int
    {
        return (int) $this->stocks()->sum('quantity');
    }

    public function getProfitMarginAttribute(): float
    {
        if ($this->cost <= 0) return 0;
        return round((($this->price - $this->cost) / $this->cost) * 100, 2);
    }
}
