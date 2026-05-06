<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('كود العميل');
            $table->string('name')->comment('اسم العميل / المحل');
            $table->string('phone', 20)->nullable();
            $table->string('alt_phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->foreignId('zone_id')->nullable()->constrained('zones')->nullOnDelete();
            $table->enum('type', ['shop', 'supermarket', 'cafe'])->default('shop')->comment('نوع العميل');
            $table->decimal('credit_limit', 12, 2)->default(0)->comment('حد الائتمان');
            $table->decimal('balance', 12, 2)->default(0)->comment('الرصيد الحالي');
            $table->decimal('location_lat', 10, 7)->nullable();
            $table->decimal('location_lng', 10, 7)->nullable();
            $table->string('contact_person')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('type');
            $table->index(['zone_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
