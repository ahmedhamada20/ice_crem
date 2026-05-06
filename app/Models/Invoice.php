<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $invoice_number
 * @property float $total
 * @property float $paid
 * @property float $balance
 * @property string $status
 */
class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_number', 'order_id', 'customer_id', 'issue_date', 'due_date',
        'subtotal', 'discount', 'tax', 'total', 'paid', 'balance', 'status', 'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'paid' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = static::generateInvoiceNumber();
            }
        });
    }

    public static function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $count = static::withTrashed()->whereYear('created_at', $year)->count() + 1;
        return sprintf('INV-%s-%05d', $year, $count);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['unpaid', 'partial', 'overdue']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())->whereIn('status', ['unpaid', 'partial']);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'paid'      => '<span class="badge bg-success">'.__('Paid').'</span>',
            'partial'   => '<span class="badge bg-warning text-dark">'.__('Partial').'</span>',
            'unpaid'    => '<span class="badge bg-secondary">'.__('Unpaid').'</span>',
            'overdue'   => '<span class="badge bg-danger">'.__('Overdue').'</span>',
            'cancelled' => '<span class="badge bg-dark">'.__('Cancelled').'</span>',
            default     => '<span class="badge bg-light text-dark">-</span>',
        };
    }

    public function getDaysOverdueAttribute(): int
    {
        if (!$this->due_date || $this->status === 'paid') return 0;
        return max(0, now()->diffInDays($this->due_date, false) * -1);
    }

    public function recalcBalance(): void
    {
        $this->paid = (float) $this->payments()->sum('amount');
        $this->balance = (float) $this->total - $this->paid;

        if ($this->balance <= 0) {
            $this->status = 'paid';
        } elseif ($this->paid > 0) {
            $this->status = 'partial';
        } else {
            $this->status = $this->due_date && $this->due_date->isPast() ? 'overdue' : 'unpaid';
        }
        $this->save();
    }
}
