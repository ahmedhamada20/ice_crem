<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Orchestrates seeding of all demo data (zones, products, customers, orders, payments, visits...).
 * Useful when you only want demo data after the base setup has run.
 *
 * Run with: php artisan db:seed --class=DemoDataSeeder
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Hard guard: refuse in production unless explicit opt-in.
        if (app()->environment('production') && env('SEED_DEMO') !== '1') {
            $this->command?->error('DemoDataSeeder refused to run in production. Set SEED_DEMO=1 to override.');
            return;
        }

        $this->call([
            ZonesSeeder::class,
            CategoriesSeeder::class,
            WarehousesSeeder::class,
            ProductsSeeder::class,
            CustomersSeeder::class,
            StockSeeder::class,
            OrdersSeeder::class,
            PaymentsSeeder::class,
            VisitsSeeder::class,
        ]);
    }
}
