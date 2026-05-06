<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'payment_number', 'invoice_id', 'customer_id', 'user_id',
        'payment_date', 'amount', 'method', 'reference', 'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->payment_number)) {
                $year = date('Y');
                $count = static::withTrashed()->whereYear('created_at', $year)->count() + 1;
                $payment->payment_number = sprintf('PAY-%s-%05d', $year, $count);
            }
        });

        static::saved(function ($payment) {
            if ($payment->invoice_id) {
                $payment->invoice?->recalcBalance();
            }
        });
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeBetweenDates($query, $from, $to)
    {
        return $query->whereBetween('payment_date', [$from, $to]);
    }

    public function getMethodLabelAttribute(): string
    {
        return match ($this->method) {
            'cash'   => __('cash'),
            'bank'   => __('bank'),
            'cheque' => __('cheque'),
            default  => $this->method,
        };
    }
}
