<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthHelper
{
    public static function user(): ?User
    {
        /** @var User|null $u */
        $u = Auth::user();
        return $u;
    }

    public static function isSuperAdmin(): bool
    {
        return self::user()?->hasRole('super-admin') ?? false;
    }

    public static function isAdmin(): bool
    {
        $u = self::user();
        return $u && $u->hasAnyRole(['super-admin', 'admin']);
    }

    public static function isZoneManager(): bool
    {
        return self::user()?->hasRole('zone-manager') ?? false;
    }

    public static function isSalesman(): bool
    {
        return self::user()?->hasRole('salesman') ?? false;
    }

    public static function isDriver(): bool
    {
        return self::user()?->hasRole('driver') ?? false;
    }

    public static function isAccountant(): bool
    {
        return self::user()?->hasRole('accountant') ?? false;
    }

    public static function isWarehouseKeeper(): bool
    {
        return self::user()?->hasRole('warehouse-keeper') ?? false;
    }

    public static function currentUserZone(): ?int
    {
        return self::user()?->zone_id;
    }

    public static function canAccessAllZones(): bool
    {
        return self::isAdmin() || self::isAccountant();
    }
}
