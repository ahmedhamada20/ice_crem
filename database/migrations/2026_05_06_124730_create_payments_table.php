<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number', 30)->unique();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->comment('من قام بالتحصيل');
            $table->date('payment_date');
            $table->decimal('amount', 12, 2);
            $table->enum('method', ['cash', 'bank', 'cheque'])->default('cash');
            $table->string('reference')->nullable()->comment('رقم الشيك أو التحويل');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('payment_date');
            $table->index(['customer_id', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
