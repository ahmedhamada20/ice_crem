<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('اسم المنطقة');
            $table->string('code', 20)->unique()->comment('كود المنطقة');
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete()->comment('مدير المنطقة');
            $table->text('notes')->nullable()->comment('ملاحظات');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zones');
    }
};
