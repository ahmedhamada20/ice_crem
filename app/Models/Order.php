<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $order_number
 * @property int $customer_id
 * @property int|null $salesman_id
 * @property string $status
 * @property float $total
 * @property float $net_total
 */
class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number', 'customer_id', 'salesman_id', 'warehouse_id',
        'order_date', 'delivery_date', 'status',
        'subtotal', 'discount', 'discount_percent', 'tax', 'tax_percent',
        'total', 'net_total', 'notes',
        'confirmed_at', 'confirmed_by', 'cancelled_at',
    ];

    protected $casts = [
        'order_date' => 'date',
        'delivery_date' => 'date',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'tax' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'total' => 'decimal:2',
        'net_total' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber();
            }
        });
    }

    public static function generateOrderNumber(): string
    {
        $year = date('Y');
        $count = static::withTrashed()->whereYear('created_at', $year)->count() + 1;
        return sprintf('ORD-%s-%05d', $year, $count);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function delivery(): HasOne
    {
        return $this->hasOne(Delivery::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('order_date', today());
    }

    public function scopeForSalesman($query, int $userId)
    {
        return $query->where('salesman_id', $userId);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending'    => '<span class="badge bg-warning text-dark">'.__('Pending').'</span>',
            'confirmed'  => '<span class="badge bg-info">'.__('Confirmed').'</span>',
            'delivering' => '<span class="badge bg-primary">'.__('Delivering').'</span>',
            'delivered'  => '<span class="badge bg-success">'.__('Delivered').'</span>',
            'cancelled'  => '<span class="badge bg-danger">'.__('Cancelled').'</span>',
            'returned'   => '<span class="badge bg-dark">مرتجع</span>',
            default      => '<span class="badge bg-secondary">-</span>',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'    => __('Pending'),
            'confirmed'  => __('Confirmed'),
            'delivering' => __('Delivering'),
            'delivered'  => __('Delivered'),
            'cancelled'  => __('Cancelled'),
            'returned'   => 'مرتجع',
            default      => $this->status,
        };
    }
}
