<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PaymentsSeeder extends Seeder
{
    public function run(): void
    {
        // Hard guard: creates fictitious payment records; never auto-run in production.
        if (app()->environment('production') && env('SEED_DEMO') !== '1') {
            $this->command?->error('PaymentsSeeder refused to run in production. Set SEED_DEMO=1 to override.');
            return;
        }

        $accountant = User::role('accountant')->first()
                    ?? User::role('admin')->first();

        $invoices = Invoice::whereIn('status', ['unpaid', 'partial', 'overdue'])->get();

        $methods = ['cash', 'cash', 'cash', 'bank', 'cheque']; // weighted

        foreach ($invoices as $invoice) {
            $rand = rand(1, 100);

            // 50% paid in full, 25% partially, 25% remain unpaid
            if ($rand <= 50) {
                $this->makePayment($invoice, $invoice->total, $methods, $accountant, $invoice->issue_date);
            } elseif ($rand <= 75) {
                $partialAmount = round($invoice->total * (rand(20, 70) / 100), 2);
                $this->makePayment($invoice, $partialAmount, $methods, $accountant, $invoice->issue_date);
            }
        }

        // Mark some unpaid invoices as overdue if past due date
        Invoice::where('status', 'unpaid')
            ->where('due_date', '<', now())
            ->update(['status' => 'overdue']);

        // Recalc customer balances after payments
        Customer::all()->each(function (Customer $c) {
            $invTotal = (float) $c->invoices()->sum('total');
            $payTotal = (float) $c->payments()->sum('amount');
            $c->update(['balance' => $invTotal - $payTotal]);
        });
    }

    private function makePayment(Invoice $invoice, float $amount, array $methods, ?User $user, $issueDate): void
    {
        $issue = Carbon::parse($issueDate);
        $payDate = $issue->copy()->addDays(rand(1, 25));

        // Don't backdate past today
        if ($payDate->isFuture()) {
            $payDate = Carbon::today();
        }

        $method = $methods[array_rand($methods)];

        Payment::create([
            'invoice_id'   => $invoice->id,
            'customer_id'  => $invoice->customer_id,
            'user_id'      => $user?->id,
            'payment_date' => $payDate->toDateString(),
            'amount'       => $amount,
            'method'       => $method,
            'reference'    => $method === 'cheque' ? 'CHK-' . rand(100000, 999999)
                            : ($method === 'bank' ? 'BNK-' . rand(100000, 999999) : null),
            'notes'        => $method === 'cash' ? 'تحصيل نقدي' : 'دفعة محصلة',
            'created_at'   => $payDate,
            'updated_at'   => $payDate,
        ]);

        // Refresh invoice (the Payment::saved hook recalcs balance, but ensure it)
        $invoice->refresh()->recalcBalance();
    }
}
