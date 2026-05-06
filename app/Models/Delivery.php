<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'delivery_number', 'order_id', 'driver_id', 'vehicle_number',
        'assigned_at', 'started_at', 'delivered_at', 'status',
        'start_lat', 'start_lng', 'end_lat', 'end_lng',
        'signature', 'photo', 'notes', 'failure_reason',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'delivered_at' => 'datetime',
        'start_lat' => 'decimal:7',
        'start_lng' => 'decimal:7',
        'end_lat' => 'decimal:7',
        'end_lng' => 'decimal:7',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($delivery) {
            if (empty($delivery->delivery_number)) {
                $year = date('Y');
                $count = static::withTrashed()->whereYear('created_at', $year)->count() + 1;
                $delivery->delivery_number = sprintf('DLV-%s-%05d', $year, $count);
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function scopeInProgress($query)
    {
        return $query->whereIn('status', ['assigned', 'in_progress']);
    }

    public function scopeForDriver($query, int $userId)
    {
        return $query->where('driver_id', $userId);
    }

    public function getDurationMinutesAttribute(): ?int
    {
        if (!$this->started_at || !$this->delivered_at) return null;
        return $this->started_at->diffInMinutes($this->delivered_at);
    }
}
