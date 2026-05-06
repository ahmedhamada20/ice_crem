<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'علب عائلية',     'description' => 'علب آيس كريم بأحجام عائلية كبيرة'],
            ['name' => 'كاسات فردية',    'description' => 'كاسات آيس كريم للفرد الواحد'],
            ['name' => 'مخروطات (كون)',  'description' => 'مخروطات بسكويت بنكهات متعددة'],
            ['name' => 'ساندوتش وحلويات','description' => 'ساندوتش آيس كريم وحلويات مجمدة'],
            ['name' => 'إستيك (آيس بوبس)','description' => 'آيس كريم على عصاية بنكهات الفاكهة'],
            ['name' => 'صبّاب (Soft Serve)','description' => 'مكعبات وصبّاب للمحلات والكافيهات'],
        ];

        foreach ($categories as $c) {
            Category::updateOrCreate(
                ['name' => $c['name']],
                array_merge($c, [
                    'slug' => Str::slug($c['name']) ?: 'cat-' . Str::random(5),
                    'is_active' => true,
                ])
            );
        }
    }
}
