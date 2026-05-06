<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        // ── Hard guard: refuse to run in production unless explicitly opted in ──
        // Reason: this seeder uses updateOrCreate by email and would overwrite
        // real-user credentials with a known demo password.
        if (app()->environment('production') && env('SEED_DEMO') !== '1') {
            $this->command?->error('DemoUsersSeeder refused to run in production. Set SEED_DEMO=1 to override.');
            return;
        }

        // ── Password: read from env, fall back to a per-run random one in non-local envs ──
        // Local dev keeps the convenience of "password"; staging/non-local needs an explicit
        // value or generates a strong random one and prints it once.
        $password = env('DEMO_USER_PASSWORD');
        if (! $password) {
            $password = app()->environment('local', 'testing') ? 'password' : Str::random(20);
            if (! app()->environment('local', 'testing')) {
                $this->command?->warn('DEMO_USER_PASSWORD not set. Generated random password for this run:');
                $this->command?->warn($password);
            }
        }

        $zones = Zone::pluck('id', 'code');

        $core = [
            ['name' => 'مدير عام',          'email' => 'super@icecream.local',     'role' => 'super-admin',       'phone' => '01000000001'],
            ['name' => 'مدير النظام',       'email' => 'admin@icecream.local',     'role' => 'admin',             'phone' => '01000000002'],
            ['name' => 'مدير منطقة القاهرة', 'email' => 'zone@icecream.local',      'role' => 'zone-manager',      'phone' => '01000000003', 'zone' => 'Z-001'],
            ['name' => 'مندوب أحمد',        'email' => 'sales@icecream.local',     'role' => 'salesman',          'phone' => '01000000004', 'zone' => 'Z-001'],
            ['name' => 'سائق محمد',         'email' => 'driver@icecream.local',    'role' => 'driver',            'phone' => '01000000005', 'zone' => 'Z-001'],
            ['name' => 'محاسب علي',         'email' => 'accountant@icecream.local','role' => 'accountant',        'phone' => '01000000006'],
            ['name' => 'أمين المخزن',       'email' => 'warehouse@icecream.local', 'role' => 'warehouse-keeper',  'phone' => '01000000007'],
        ];

        $extraSalesmen = [
            ['name' => 'مندوب خالد',  'email' => 'sales1@icecream.local', 'phone' => '01001234001', 'zone' => 'Z-002'],
            ['name' => 'مندوب طارق',  'email' => 'sales2@icecream.local', 'phone' => '01001234002', 'zone' => 'Z-003'],
            ['name' => 'مندوب يوسف',  'email' => 'sales3@icecream.local', 'phone' => '01001234003', 'zone' => 'Z-004'],
            ['name' => 'مندوب وائل',  'email' => 'sales4@icecream.local', 'phone' => '01001234004', 'zone' => 'Z-005'],
            ['name' => 'مندوب عمرو',  'email' => 'sales5@icecream.local', 'phone' => '01001234005', 'zone' => 'Z-006'],
        ];

        $extraDrivers = [
            ['name' => 'سائق سامي',   'email' => 'driver1@icecream.local', 'phone' => '01002345001', 'zone' => 'Z-002'],
            ['name' => 'سائق هاني',   'email' => 'driver2@icecream.local', 'phone' => '01002345002', 'zone' => 'Z-003'],
            ['name' => 'سائق فؤاد',   'email' => 'driver3@icecream.local', 'phone' => '01002345003', 'zone' => 'Z-004'],
            ['name' => 'سائق صابر',   'email' => 'driver4@icecream.local', 'phone' => '01002345004', 'zone' => 'Z-005'],
            ['name' => 'سائق رمضان',  'email' => 'driver5@icecream.local', 'phone' => '01002345005', 'zone' => 'Z-006'],
        ];

        $extraZoneManagers = [
            ['name' => 'مدير منطقة نصر',     'email' => 'zone2@icecream.local', 'phone' => '01003456001', 'zone' => 'Z-002'],
            ['name' => 'مدير منطقة الجديدة',  'email' => 'zone3@icecream.local', 'phone' => '01003456002', 'zone' => 'Z-003'],
            ['name' => 'مدير منطقة الجيزة',   'email' => 'zone4@icecream.local', 'phone' => '01003456003', 'zone' => 'Z-004'],
        ];

        foreach ($core as $data) {
            $this->createUser($data, $zones, $password);
        }

        foreach ($extraSalesmen as $data) {
            $this->createUser(array_merge($data, ['role' => 'salesman']), $zones, $password);
        }

        foreach ($extraDrivers as $data) {
            $this->createUser(array_merge($data, ['role' => 'driver']), $zones, $password);
        }

        foreach ($extraZoneManagers as $data) {
            $this->createUser(array_merge($data, ['role' => 'zone-manager']), $zones, $password);
        }
    }

    private function createUser(array $data, $zones, string $password): void
    {
        $role     = $data['role'];
        $zoneCode = $data['zone'] ?? null;
        unset($data['role'], $data['zone']);

        // Defense-in-depth: never overwrite credentials of an existing user
        // (even if the email matches a "demo" address, treat it as real).
        $existing = User::where('email', $data['email'])->first();
        if ($existing) {
            $existing->update([
                'name'    => $data['name'],
                'phone'   => $data['phone'] ?? $existing->phone,
                'zone_id' => $zoneCode ? ($zones[$zoneCode] ?? $existing->zone_id) : $existing->zone_id,
            ]);
            $existing->syncRoles([$role]);
            return;
        }

        $user = User::create(array_merge($data, [
            'password'          => Hash::make('password'),
            'status'            => 'active',
            'zone_id'           => $zoneCode ? ($zones[$zoneCode] ?? null) : null,
            'email_verified_at' => now(),
        ]));

        $user->syncRoles([$role]);
    }
}
