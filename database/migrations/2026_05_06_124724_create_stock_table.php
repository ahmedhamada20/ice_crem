<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->integer('quantity')->default(0)->comment('الكمية المتاحة');
            $table->integer('reserved')->default(0)->comment('الكمية المحجوزة');
            $table->timestamps();

            $table->unique(['product_id', 'warehouse_id']);
            $table->index('quantity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock');
    }
};
