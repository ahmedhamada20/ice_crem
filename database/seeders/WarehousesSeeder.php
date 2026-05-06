<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehousesSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = [
            [
                'code'    => 'WH-MAIN',
                'name'    => 'المستودع الرئيسي - السلام',
                'address' => 'مدينة السلام، شارع المدارس، أمام مصنع الإنتاج',
                'phone'   => '02-22871234',
                'is_main' => true,
            ],
            [
                'code'    => 'WH-CAI',
                'name'    => 'مستودع القاهرة',
                'address' => 'العباسية، شارع الكاتدرائية',
                'phone'   => '02-26831234',
                'is_main' => false,
            ],
            [
                'code'    => 'WH-GIZA',
                'name'    => 'مستودع الجيزة',
                'address' => 'فيصل، شارع الفلاح المتفرع من الهرم',
                'phone'   => '02-37631234',
                'is_main' => false,
            ],
            [
                'code'    => 'WH-ALEX',
                'name'    => 'مستودع الإسكندرية',
                'address' => 'سموحة، طريق الإسكندرية الزراعي',
                'phone'   => '03-42351234',
                'is_main' => false,
            ],
        ];

        foreach ($warehouses as $w) {
            Warehouse::updateOrCreate(['code' => $w['code']], array_merge($w, ['is_active' => true]));
        }
    }
}
