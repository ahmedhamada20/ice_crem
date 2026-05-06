<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Support\Collection;

class CustomerService
{
    public function createCustomer(array $data): Customer
    {
        return Customer::create($data);
    }

    public function updateCustomer(Customer $customer, array $data): Customer
    {
        $customer->update($data);
        return $customer->fresh();
    }

    public function calculateBalance(Customer $customer): float
    {
        $invoicesTotal = (float) $customer->invoices()->sum('total');
        $paymentsTotal = (float) $customer->payments()->sum('amount');
        $balance = $invoicesTotal - $paymentsTotal;

        $customer->update(['balance' => $balance]);

        return $balance;
    }

    public function getCustomerStatement(Customer $customer, ?string $from = null, ?string $to = null): Collection
    {
        $rows = collect();

        $invoiceQ = $customer->invoices();
        $paymentQ = $customer->payments();

        if ($from) {
            $invoiceQ->where('issue_date', '>=', $from);
            $paymentQ->where('payment_date', '>=', $from);
        }
        if ($to) {
            $invoiceQ->where('issue_date', '<=', $to);
            $paymentQ->where('payment_date', '<=', $to);
        }

        foreach ($invoiceQ->get() as $inv) {
            $rows->push([
                'date'    => $inv->issue_date,
                'type'    => 'invoice',
                'ref'     => $inv->invoice_number,
                'debit'   => (float) $inv->total,
                'credit'  => 0,
                'notes'   => $inv->notes,
            ]);
        }

        foreach ($paymentQ->get() as $pay) {
            $rows->push([
                'date'    => $pay->payment_date,
                'type'    => 'payment',
                'ref'     => $pay->payment_number,
                'debit'   => 0,
                'credit'  => (float) $pay->amount,
                'notes'   => $pay->notes,
            ]);
        }

        $sorted = $rows->sortBy('date')->values();

        $running = 0;
        return $sorted->map(function ($row) use (&$running) {
            $running += $row['debit'] - $row['credit'];
            $row['balance'] = $running;
            return $row;
        });
    }
}
