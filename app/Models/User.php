<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property int|null $zone_id
 * @property string $status
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes, HasApiTokens;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'zone_id', 'status', 'avatar',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function managedZones(): HasMany
    {
        return $this->hasMany(Zone::class, 'manager_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'salesman_id');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class, 'driver_id');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class, 'salesman_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSalesmen($query)
    {
        return $query->role('salesman');
    }

    public function scopeDrivers($query)
    {
        return $query->role('driver');
    }
}
