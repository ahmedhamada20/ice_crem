<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super-admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('orders.view');
    }

    public function view(User $user, Order $order): bool
    {
        if (!$user->can('orders.view')) return false;
        if ($user->hasAnyRole(['admin', 'accountant'])) return true;

        if ($user->hasRole('salesman')) {
            return $order->salesman_id === $user->id;
        }

        if ($user->hasRole('zone-manager')) {
            return $order->customer && $order->customer->zone_id === $user->zone_id;
        }

        if ($user->hasRole('driver')) {
            return $order->delivery && $order->delivery->driver_id === $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can('orders.create');
    }

    public function update(User $user, Order $order): bool
    {
        return $user->can('orders.edit')
            && in_array($order->status, ['pending'])
            && $this->view($user, $order);
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->can('orders.delete') && $user->hasAnyRole(['admin']);
    }

    public function confirm(User $user, Order $order): bool
    {
        return $user->can('orders.confirm') && $order->status === 'pending';
    }

    public function cancel(User $user, Order $order): bool
    {
        return $user->can('orders.cancel')
            && in_array($order->status, ['pending', 'confirmed']);
    }
}
