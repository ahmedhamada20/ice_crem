<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_number', 30)->unique();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('vehicle_number')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->enum('status', ['assigned', 'in_progress', 'delivered', 'failed', 'returned'])->default('assigned');
            $table->decimal('start_lat', 10, 7)->nullable();
            $table->decimal('start_lng', 10, 7)->nullable();
            $table->decimal('end_lat', 10, 7)->nullable();
            $table->decimal('end_lng', 10, 7)->nullable();
            $table->string('signature')->nullable()->comment('مسار صورة التوقيع');
            $table->string('photo')->nullable()->comment('صورة إثبات التسليم');
            $table->text('notes')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index(['driver_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
