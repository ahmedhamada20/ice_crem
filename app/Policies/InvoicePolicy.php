<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super-admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('invoices.view');
    }

    public function view(User $user, Invoice $invoice): bool
    {
        if (!$user->can('invoices.view')) return false;
        if ($user->hasAnyRole(['admin', 'accountant'])) return true;

        if ($user->hasRole('zone-manager')) {
            return $invoice->customer && $invoice->customer->zone_id === $user->zone_id;
        }

        if ($user->hasRole('salesman')) {
            return $invoice->order && $invoice->order->salesman_id === $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can('invoices.create');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->can('invoices.edit') && $invoice->status !== 'paid';
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->can('invoices.delete') && $user->hasRole('admin');
    }

    public function markPaid(User $user, Invoice $invoice): bool
    {
        return $user->can('invoices.markpaid');
    }
}
