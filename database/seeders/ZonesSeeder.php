<?php

namespace Database\Seeders;

use App\Models\Zone;
use Illuminate\Database\Seeder;

class ZonesSeeder extends Seeder
{
    public function run(): void
    {
        $zones = [
            ['code' => 'Z-001', 'name' => 'القاهرة الكبرى',  'notes' => 'وسط القاهرة، الزمالك، جاردن سيتي، الدقي، المهندسين'],
            ['code' => 'Z-002', 'name' => 'مدينة نصر',       'notes' => 'مدينة نصر، الحي السابع، الحي العاشر'],
            ['code' => 'Z-003', 'name' => 'مصر الجديدة',     'notes' => 'مصر الجديدة، روكسي، شيراتون'],
            ['code' => 'Z-004', 'name' => 'الجيزة',          'notes' => 'الجيزة، فيصل، الهرم، أكتوبر'],
            ['code' => 'Z-005', 'name' => 'المعادي والمقطم', 'notes' => 'المعادي، المقطم، حلوان'],
            ['code' => 'Z-006', 'name' => 'الإسكندرية',      'notes' => 'الإسكندرية بأحيائها كلها'],
            ['code' => 'Z-007', 'name' => 'الدلتا',          'notes' => 'المنصورة، طنطا، المحلة، شبرا'],
        ];

        foreach ($zones as $z) {
            Zone::updateOrCreate(['code' => $z['code']], array_merge($z, ['is_active' => true]));
        }
    }
}
