<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class VisitsSeeder extends Seeder
{
    public function run(): void
    {
        // Hard guard: creates 80 fictitious visit records.
        if (app()->environment('production') && env('SEED_DEMO') !== '1') {
            $this->command?->error('VisitsSeeder refused to run in production. Set SEED_DEMO=1 to override.');
            return;
        }

        $salesmen  = User::role('salesman')->get();
        $customers = Customer::active()->get();

        if ($salesmen->isEmpty() || $customers->isEmpty()) {
            $this->command->warn('VisitsSeeder skipped: missing salesmen or customers.');
            return;
        }

        $results = ['order_placed', 'no_order', 'rescheduled', 'closed'];

        // ~80 visits across last 60 days
        for ($i = 0; $i < 80; $i++) {
            $salesman   = $salesmen->random();
            $customer   = $customers->random();
            $visitDate  = Carbon::now()->subDays(rand(0, 60))->setTime(rand(9, 16), rand(0, 59));
            $checkOut   = $visitDate->copy()->addMinutes(rand(15, 90));

            $result = $results[array_rand($results)];

            // Try to link to an order on the same day if result == order_placed
            $orderId = null;
            if ($result === 'order_placed') {
                $orderId = Order::where('customer_id', $customer->id)
                    ->where('salesman_id', $salesman->id)
                    ->whereDate('order_date', $visitDate->toDateString())
                    ->value('id');
            }

            // Add small jitter around customer's location for the check-in/out coordinates
            $baseLat = $customer->location_lat ?: 30.0444;
            $baseLng = $customer->location_lng ?: 31.2357;

            Visit::create([
                'salesman_id'    => $salesman->id,
                'customer_id'    => $customer->id,
                'visit_date'     => $visitDate->toDateString(),
                'check_in'       => $visitDate,
                'check_out'      => $checkOut,
                'check_in_lat'   => $baseLat + (rand(-50, 50) / 10000),
                'check_in_lng'   => $baseLng + (rand(-50, 50) / 10000),
                'check_out_lat'  => $baseLat + (rand(-50, 50) / 10000),
                'check_out_lng'  => $baseLng + (rand(-50, 50) / 10000),
                'result'         => $result,
                'order_id'       => $orderId,
                'notes'          => match ($result) {
                    'order_placed'  => 'العميل اشترى وعاد بطلب جيد',
                    'no_order'      => 'لم يحتج لطلب جديد، المخزون كافٍ',
                    'rescheduled'   => 'مدير المحل غير متواجد، إعادة الزيارة الأسبوع القادم',
                    'closed'        => 'المحل مغلق',
                    default         => null,
                },
                'created_at'     => $visitDate,
                'updated_at'     => $checkOut,
            ]);
        }
    }
}
