<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super-admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('customers.view');
    }

    public function view(User $user, Customer $customer): bool
    {
        if (!$user->can('customers.view')) return false;

        if ($user->hasAnyRole(['admin', 'accountant'])) return true;

        if ($user->hasRole('zone-manager') || $user->hasRole('salesman')) {
            return $user->zone_id && $customer->zone_id === $user->zone_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can('customers.create');
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->can('customers.edit') && $this->view($user, $customer);
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->can('customers.delete') && $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function restore(User $user, Customer $customer): bool
    {
        return $user->hasRole('admin');
    }

    public function forceDelete(User $user, Customer $customer): bool
    {
        return false;
    }
}
