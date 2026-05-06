<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Always safe (production-ready setup data) ─────────────────────
        $this->call([
            RolesAndPermissionsSeeder::class,
            ZonesSeeder::class,
            CategoriesSeeder::class,
            WarehousesSeeder::class,
            ProductsSeeder::class,
        ]);

        // ── Demo / fixture data (NEVER auto-run in production) ────────────
        if (app()->environment('production')) {
            $this->command?->warn('Demo seeders skipped — production environment detected.');
            $this->command?->warn('To seed demo data anyway, run each demo seeder explicitly with the SEED_DEMO=1 env flag.');
            return;
        }

        $this->call([
            DemoUsersSeeder::class,
            CustomersSeeder::class,
            StockSeeder::class,
            OrdersSeeder::class,
            PaymentsSeeder::class,
            VisitsSeeder::class,
        ]);
    }
}
