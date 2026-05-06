<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    public function run(): void
    {
        $cats = Category::pluck('id', 'name');

        $products = [
            // علب عائلية
            ['code' => 'P-F001', 'cat' => 'علب عائلية', 'name' => 'علبة فانيليا 1 لتر',           'unit' => 'علبة', 'price' => 95,  'cost' => 55,  'min' => 20],
            ['code' => 'P-F002', 'cat' => 'علب عائلية', 'name' => 'علبة شوكولاتة 1 لتر',          'unit' => 'علبة', 'price' => 105, 'cost' => 62,  'min' => 20],
            ['code' => 'P-F003', 'cat' => 'علب عائلية', 'name' => 'علبة فراولة 1 لتر',            'unit' => 'علبة', 'price' => 100, 'cost' => 60,  'min' => 15],
            ['code' => 'P-F004', 'cat' => 'علب عائلية', 'name' => 'علبة مانجو 1 لتر',             'unit' => 'علبة', 'price' => 110, 'cost' => 65,  'min' => 15],
            ['code' => 'P-F005', 'cat' => 'علب عائلية', 'name' => 'علبة كوكيز كريم 1 لتر',        'unit' => 'علبة', 'price' => 115, 'cost' => 68,  'min' => 15],
            ['code' => 'P-F006', 'cat' => 'علب عائلية', 'name' => 'علبة بستاشيو 1 لتر',           'unit' => 'علبة', 'price' => 130, 'cost' => 80,  'min' => 10],
            ['code' => 'P-F007', 'cat' => 'علب عائلية', 'name' => 'علبة كاراميل 1 لتر',           'unit' => 'علبة', 'price' => 110, 'cost' => 65,  'min' => 12],

            // كاسات فردية
            ['code' => 'P-C001', 'cat' => 'كاسات فردية', 'name' => 'كاسة فانيليا 100 جم',         'unit' => 'كاسة', 'price' => 12, 'cost' => 5.5, 'min' => 100],
            ['code' => 'P-C002', 'cat' => 'كاسات فردية', 'name' => 'كاسة شوكولاتة 100 جم',        'unit' => 'كاسة', 'price' => 13, 'cost' => 6,   'min' => 100],
            ['code' => 'P-C003', 'cat' => 'كاسات فردية', 'name' => 'كاسة فراولة 100 جم',          'unit' => 'كاسة', 'price' => 13, 'cost' => 6,   'min' => 80],
            ['code' => 'P-C004', 'cat' => 'كاسات فردية', 'name' => 'كاسة كاراميل 100 جم',         'unit' => 'كاسة', 'price' => 14, 'cost' => 7,   'min' => 60],
            ['code' => 'P-C005', 'cat' => 'كاسات فردية', 'name' => 'كاسة كوكيز كريم 100 جم',      'unit' => 'كاسة', 'price' => 15, 'cost' => 7.5, 'min' => 60],

            // مخروطات
            ['code' => 'P-K001', 'cat' => 'مخروطات (كون)', 'name' => 'كون فانيليا كبير',          'unit' => 'كون', 'price' => 18, 'cost' => 9,   'min' => 80],
            ['code' => 'P-K002', 'cat' => 'مخروطات (كون)', 'name' => 'كون شوكولاتة كبير',         'unit' => 'كون', 'price' => 20, 'cost' => 10,  'min' => 80],
            ['code' => 'P-K003', 'cat' => 'مخروطات (كون)', 'name' => 'كون مانجو',                 'unit' => 'كون', 'price' => 22, 'cost' => 11,  'min' => 50],
            ['code' => 'P-K004', 'cat' => 'مخروطات (كون)', 'name' => 'كون فراولة',                'unit' => 'كون', 'price' => 20, 'cost' => 10,  'min' => 50],

            // ساندوتش وحلويات
            ['code' => 'P-S001', 'cat' => 'ساندوتش وحلويات', 'name' => 'ساندوتش بسكويت فانيليا',  'unit' => 'قطعة', 'price' => 16, 'cost' => 7.5, 'min' => 100],
            ['code' => 'P-S002', 'cat' => 'ساندوتش وحلويات', 'name' => 'ساندوتش شوكولاتة براوني', 'unit' => 'قطعة', 'price' => 18, 'cost' => 9,   'min' => 80],
            ['code' => 'P-S003', 'cat' => 'ساندوتش وحلويات', 'name' => 'تشوكو بار بالبندق',        'unit' => 'قطعة', 'price' => 22, 'cost' => 12,  'min' => 60],

            // إستيك
            ['code' => 'P-I001', 'cat' => 'إستيك (آيس بوبس)', 'name' => 'إستيك مانجو',            'unit' => 'قطعة', 'price' => 8,  'cost' => 3.5, 'min' => 200],
            ['code' => 'P-I002', 'cat' => 'إستيك (آيس بوبس)', 'name' => 'إستيك فراولة',           'unit' => 'قطعة', 'price' => 8,  'cost' => 3.5, 'min' => 200],
            ['code' => 'P-I003', 'cat' => 'إستيك (آيس بوبس)', 'name' => 'إستيك ليمون',            'unit' => 'قطعة', 'price' => 7,  'cost' => 3,   'min' => 200],
            ['code' => 'P-I004', 'cat' => 'إستيك (آيس بوبس)', 'name' => 'إستيك حليب بالشوكولاتة', 'unit' => 'قطعة', 'price' => 10, 'cost' => 4.5, 'min' => 150],

            // صبّاب
            ['code' => 'P-B001', 'cat' => 'صبّاب (Soft Serve)', 'name' => 'كرتونة فانيليا 5 لتر',  'unit' => 'كرتونة', 'price' => 380, 'cost' => 240, 'min' => 8],
            ['code' => 'P-B002', 'cat' => 'صبّاب (Soft Serve)', 'name' => 'كرتونة شوكولاتة 5 لتر', 'unit' => 'كرتونة', 'price' => 420, 'cost' => 270, 'min' => 8],
            ['code' => 'P-B003', 'cat' => 'صبّاب (Soft Serve)', 'name' => 'كرتونة فراولة 5 لتر',   'unit' => 'كرتونة', 'price' => 410, 'cost' => 260, 'min' => 5],
            ['code' => 'P-B004', 'cat' => 'صبّاب (Soft Serve)', 'name' => 'مكعبات للمحلات 10 كجم', 'unit' => 'كرتونة', 'price' => 750, 'cost' => 480, 'min' => 4],
        ];

        foreach ($products as $p) {
            Product::updateOrCreate(['code' => $p['code']], [
                'name'        => $p['name'],
                'category_id' => $cats[$p['cat']] ?? null,
                'unit'        => $p['unit'],
                'price'       => $p['price'],
                'cost'        => $p['cost'],
                'min_stock'   => $p['min'],
                'is_active'   => true,
            ]);
        }
    }
}
