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
 * @property string|null $phone
 * @property string|null $address
 * @property int|null $zone_id
 * @property string $type
 * @property float $credit_limit
 * @property float $balance
 * @property string $status
 */
class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'name', 'phone', 'alt_phone', 'email', 'address',
        'zone_id', 'type', 'credit_limit', 'balance',
        'location_lat', 'location_lng', 'contact_person', 'notes', 'status',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'balance' => 'decimal:2',
        'location_lat' => 'decimal:7',
        'location_lng' => 'decimal:7',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($customer) {
            if (empty($customer->code)) {
                $last = static::withTrashed()->latest('id')->first();
                $next = $last ? $last->id + 1 : 1;
                $customer->code = 'CUS-'.str_pad($next, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForZone($query, int $zoneId)
    {
        return $query->where('zone_id', $zoneId);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active'   => '<span class="badge bg-success">'.__('Active').'</span>',
            'inactive' => '<span class="badge bg-secondary">'.__('Inactive').'</span>',
            'blocked'  => '<span class="badge bg-danger">محظور</span>',
            default    => '<span class="badge bg-light text-dark">-</span>',
        };
    }

    public function getCreditAvailableAttribute(): float
    {
        return (float) ($this->credit_limit - $this->balance);
    }
}
