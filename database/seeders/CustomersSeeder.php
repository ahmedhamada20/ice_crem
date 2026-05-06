<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Zone;
use Illuminate\Database\Seeder;

class CustomersSeeder extends Seeder
{
    public function run(): void
    {
        // Hard guard: this seeder creates fixture customers and must not run
        // in production unless explicitly opted in.
        if (app()->environment('production') && env('SEED_DEMO') !== '1') {
            $this->command?->error('CustomersSeeder refused to run in production. Set SEED_DEMO=1 to override.');
            return;
        }

        $zones = Zone::pluck('id', 'code');

        $customers = [
            // القاهرة الكبرى (Z-001)
            ['name' => 'سوبر ماركت العائلة - الزمالك',  'phone' => '01001234567', 'type' => 'supermarket', 'zone' => 'Z-001', 'address' => '26 شارع حسن صبري، الزمالك',         'lat' => 30.0626, 'lng' => 31.2197, 'credit' => 50000],
            ['name' => 'كافيه نسيم البحر',                'phone' => '01112345678', 'type' => 'cafe',        'zone' => 'Z-001', 'address' => 'كورنيش النيل، جاردن سيتي',         'lat' => 30.0411, 'lng' => 31.2294, 'credit' => 15000],
            ['name' => 'محل الخير - الدقي',              'phone' => '01023456789', 'type' => 'shop',        'zone' => 'Z-001', 'address' => '14 شارع التحرير، الدقي',           'lat' => 30.0398, 'lng' => 31.2089, 'credit' => 10000],
            ['name' => 'سوبر ماركت بيوغرت',               'phone' => '01234567890', 'type' => 'supermarket', 'zone' => 'Z-001', 'address' => 'ميدان لبنان، المهندسين',           'lat' => 30.0594, 'lng' => 31.2110, 'credit' => 40000],
            ['name' => 'كوفي شوب لاتيه',                  'phone' => '01045678901', 'type' => 'cafe',        'zone' => 'Z-001', 'address' => 'شارع جامعة الدول العربية',         'lat' => 30.0566, 'lng' => 31.2003, 'credit' => 12000],
            ['name' => 'محل أبو محمد',                    'phone' => '01156789012', 'type' => 'shop',        'zone' => 'Z-001', 'address' => 'شارع جمعية الأهرام، باب اللوق',    'lat' => 30.0444, 'lng' => 31.2357, 'credit' => 8000],
            ['name' => 'مكتبة وكوفي بوك',                 'phone' => '01267890123', 'type' => 'cafe',        'zone' => 'Z-001', 'address' => 'وسط البلد، شارع طلعت حرب',         'lat' => 30.0489, 'lng' => 31.2417, 'credit' => 18000],

            // مدينة نصر (Z-002)
            ['name' => 'سوبر ماركت بست واي',              'phone' => '01078901234', 'type' => 'supermarket', 'zone' => 'Z-002', 'address' => 'الحي السابع، شارع عباس العقاد',     'lat' => 30.0565, 'lng' => 31.3416, 'credit' => 60000],
            ['name' => 'كافيه ستارز',                     'phone' => '01089012345', 'type' => 'cafe',        'zone' => 'Z-002', 'address' => 'شارع مكرم عبيد',                   'lat' => 30.0644, 'lng' => 31.3328, 'credit' => 20000],
            ['name' => 'محل البركة',                      'phone' => '01190123456', 'type' => 'shop',        'zone' => 'Z-002', 'address' => 'الحي العاشر، مسجد رابعة',          'lat' => 30.0731, 'lng' => 31.3441, 'credit' => 9000],
            ['name' => 'كافي تو جو',                      'phone' => '01201234567', 'type' => 'cafe',        'zone' => 'Z-002', 'address' => 'شارع الطيران',                     'lat' => 30.0680, 'lng' => 31.3340, 'credit' => 14000],
            ['name' => 'سوبر ماركت نصر سيتي',              'phone' => '01012345670', 'type' => 'supermarket', 'zone' => 'Z-002', 'address' => 'سيتي ستارز مول',                  'lat' => 30.0721, 'lng' => 31.3464, 'credit' => 80000],

            // مصر الجديدة (Z-003)
            ['name' => 'سوبر ماركت روكسي',                'phone' => '01123456701', 'type' => 'supermarket', 'zone' => 'Z-003', 'address' => 'ميدان روكسي',                      'lat' => 30.0876, 'lng' => 31.3257, 'credit' => 45000],
            ['name' => 'كافيه ميلانو',                    'phone' => '01234567802', 'type' => 'cafe',        'zone' => 'Z-003', 'address' => 'شارع الحجاز',                      'lat' => 30.1031, 'lng' => 31.3417, 'credit' => 16000],
            ['name' => 'محل العمدة',                      'phone' => '01045678903', 'type' => 'shop',        'zone' => 'Z-003', 'address' => 'شارع كليوباترا',                   'lat' => 30.1065, 'lng' => 31.3340, 'credit' => 7000],
            ['name' => 'كافيه شيراتون',                   'phone' => '01156789014', 'type' => 'cafe',        'zone' => 'Z-003', 'address' => 'شيراتون هليوبوليس',                'lat' => 30.0891, 'lng' => 31.3678, 'credit' => 22000],

            // الجيزة (Z-004)
            ['name' => 'سوبر ماركت فيصل',                 'phone' => '01267890125', 'type' => 'supermarket', 'zone' => 'Z-004', 'address' => 'فيصل، شارع الملك فيصل',            'lat' => 30.0150, 'lng' => 31.1707, 'credit' => 35000],
            ['name' => 'محل الهرم',                       'phone' => '01078901236', 'type' => 'shop',        'zone' => 'Z-004', 'address' => 'شارع الهرم',                       'lat' => 29.9876, 'lng' => 31.1466, 'credit' => 9500],
            ['name' => 'كافيه أكتوبر',                    'phone' => '01089012347', 'type' => 'cafe',        'zone' => 'Z-004', 'address' => '6 أكتوبر، الحي المتميز',          'lat' => 29.9285, 'lng' => 30.9265, 'credit' => 18000],
            ['name' => 'سوبر ماركت الشيخ زايد',            'phone' => '01190123458', 'type' => 'supermarket', 'zone' => 'Z-004', 'address' => 'الشيخ زايد، الحي الثالث',          'lat' => 30.0744, 'lng' => 30.9706, 'credit' => 55000],
            ['name' => 'محل المجد',                       'phone' => '01201234569', 'type' => 'shop',        'zone' => 'Z-004', 'address' => 'الجيزة، شارع المنصور',             'lat' => 30.0131, 'lng' => 31.2089, 'credit' => 6500],

            // المعادي والمقطم (Z-005)
            ['name' => 'سوبر ماركت المعادي',              'phone' => '01012345670', 'type' => 'supermarket', 'zone' => 'Z-005', 'address' => 'المعادي، شارع 9',                  'lat' => 29.9595, 'lng' => 31.2580, 'credit' => 50000],
            ['name' => 'كافيه المقطم هايتس',              'phone' => '01023456791', 'type' => 'cafe',        'zone' => 'Z-005', 'address' => 'المقطم، الحي الثامن',              'lat' => 29.9881, 'lng' => 31.3158, 'credit' => 14000],
            ['name' => 'محل الرحمة',                      'phone' => '01134567802', 'type' => 'shop',        'zone' => 'Z-005', 'address' => 'حلوان، شارع المنصور',              'lat' => 29.8473, 'lng' => 31.3320, 'credit' => 7500],

            // الإسكندرية (Z-006)
            ['name' => 'سوبر ماركت سموحة',                'phone' => '01245678013', 'type' => 'supermarket', 'zone' => 'Z-006', 'address' => 'سموحة، شارع فيكتور عمانوئيل',      'lat' => 31.2078, 'lng' => 29.9476, 'credit' => 70000],
            ['name' => 'كافيه البحر',                     'phone' => '01056789124', 'type' => 'cafe',        'zone' => 'Z-006', 'address' => 'كورنيش البحر، سان ستيفانو',        'lat' => 31.2424, 'lng' => 29.9744, 'credit' => 25000],
            ['name' => 'محل العصافرة',                    'phone' => '01067890235', 'type' => 'shop',        'zone' => 'Z-006', 'address' => 'العصافرة، شارع 45',                'lat' => 31.2802, 'lng' => 30.0226, 'credit' => 8000],

            // الدلتا (Z-007)
            ['name' => 'سوبر ماركت المنصورة',              'phone' => '01178901346', 'type' => 'supermarket', 'zone' => 'Z-007', 'address' => 'المنصورة، شارع الجمهورية',         'lat' => 31.0364, 'lng' => 31.3807, 'credit' => 30000],
            ['name' => 'كافيه طنطا تاون',                 'phone' => '01289012457', 'type' => 'cafe',        'zone' => 'Z-007', 'address' => 'طنطا، شارع البحر',                 'lat' => 30.7865, 'lng' => 31.0004, 'credit' => 12000],
            ['name' => 'محل الإخلاص',                     'phone' => '01090123568', 'type' => 'shop',        'zone' => 'Z-007', 'address' => 'المحلة الكبرى، شارع 23 يوليو',     'lat' => 30.9697, 'lng' => 31.1681, 'credit' => 6000],
        ];

        $i = 1;
        foreach ($customers as $c) {
            Customer::updateOrCreate(
                ['code' => 'CUS-' . str_pad((string) $i, 5, '0', STR_PAD_LEFT)],
                [
                    'name'           => $c['name'],
                    'phone'          => $c['phone'],
                    'address'        => $c['address'],
                    'zone_id'        => $zones[$c['zone']] ?? null,
                    'type'           => $c['type'],
                    'credit_limit'   => $c['credit'],
                    'balance'        => 0,
                    'location_lat'   => $c['lat'],
                    'location_lng'   => $c['lng'],
                    'contact_person' => 'مسؤول المحل',
                    'status'         => 'active',
                ]
            );
            $i++;
        }
    }
}
