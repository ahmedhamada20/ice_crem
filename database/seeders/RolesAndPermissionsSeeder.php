<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $modules = [
            'zones', 'customers', 'products', 'categories', 'warehouses',
            'orders', 'deliveries', 'invoices', 'payments', 'stock',
            'visits', 'reports', 'users',
        ];

        $actions = ['view', 'create', 'edit', 'delete', 'export'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$module}.{$action}",
                    'guard_name' => 'web',
                ]);
            }
        }

        // Special permissions
        Permission::firstOrCreate(['name' => 'orders.confirm', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'orders.cancel',  'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'deliveries.assign', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'deliveries.complete', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'invoices.markpaid', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'stock.adjust', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'stock.transfer', 'guard_name' => 'web']);

        // Roles
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $admin      = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $zoneMgr    = Role::firstOrCreate(['name' => 'zone-manager', 'guard_name' => 'web']);
        $salesman   = Role::firstOrCreate(['name' => 'salesman', 'guard_name' => 'web']);
        $driver     = Role::firstOrCreate(['name' => 'driver', 'guard_name' => 'web']);
        $accountant = Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'web']);
        $whKeeper   = Role::firstOrCreate(['name' => 'warehouse-keeper', 'guard_name' => 'web']);

        $superAdmin->syncPermissions(Permission::all());

        $admin->syncPermissions(Permission::where('name', 'not like', 'users.%')->get());

        $zoneMgr->syncPermissions(Permission::whereIn('name', [
            'customers.view', 'customers.create', 'customers.edit', 'customers.export',
            'orders.view', 'orders.create', 'orders.edit', 'orders.confirm',
            'deliveries.view', 'deliveries.assign',
            'invoices.view', 'payments.view', 'payments.create',
            'visits.view', 'reports.view',
            'products.view', 'stock.view',
        ])->get());

        $salesman->syncPermissions(Permission::whereIn('name', [
            'customers.view', 'customers.create', 'customers.edit',
            'orders.view', 'orders.create',
            'visits.view', 'visits.create', 'visits.edit',
            'products.view', 'stock.view',
            'invoices.view', 'payments.view', 'payments.create',
        ])->get());

        $driver->syncPermissions(Permission::whereIn('name', [
            'deliveries.view', 'deliveries.complete',
            'orders.view', 'customers.view',
        ])->get());

        $accountant->syncPermissions(Permission::whereIn('name', [
            'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.export', 'invoices.markpaid',
            'payments.view', 'payments.create', 'payments.edit', 'payments.delete', 'payments.export',
            'customers.view', 'orders.view',
            'reports.view', 'reports.export',
        ])->get());

        $whKeeper->syncPermissions(Permission::whereIn('name', [
            'warehouses.view', 'warehouses.create', 'warehouses.edit',
            'products.view', 'products.create', 'products.edit',
            'stock.view', 'stock.create', 'stock.edit', 'stock.adjust', 'stock.transfer', 'stock.export',
            'orders.view',
        ])->get());

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
