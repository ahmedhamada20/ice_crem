<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('كود المنتج');
            $table->string('name')->comment('اسم المنتج');
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('unit', 30)->default('علبة')->comment('الوحدة');
            $table->decimal('price', 10, 2)->default(0)->comment('سعر البيع');
            $table->decimal('cost', 10, 2)->default(0)->comment('التكلفة');
            $table->integer('min_stock')->default(0)->comment('الحد الأدنى للمخزون');
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
            $table->index('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
