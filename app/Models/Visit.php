<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Visit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'salesman_id', 'customer_id', 'visit_date', 'check_in', 'check_out',
        'check_in_lat', 'check_in_lng', 'check_out_lat', 'check_out_lng',
        'result', 'order_id', 'notes',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'check_in_lat' => 'decimal:7',
        'check_in_lng' => 'decimal:7',
        'check_out_lat' => 'decimal:7',
        'check_out_lng' => 'decimal:7',
    ];

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('visit_date', today());
    }

    public function getDurationMinutesAttribute(): ?int
    {
        if (!$this->check_in || !$this->check_out) return null;
        return $this->check_in->diffInMinutes($this->check_out);
    }
}
