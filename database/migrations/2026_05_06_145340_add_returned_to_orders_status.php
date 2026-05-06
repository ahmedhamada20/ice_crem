<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modify the enum to add 'returned' as a valid status.
        // (Laravel's Schema doesn't natively support enum modifications, so use raw SQL.)
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'confirmed', 'delivering', 'delivered', 'cancelled', 'returned') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Revert any 'returned' rows so the enum can shrink safely
        DB::table('orders')->where('status', 'returned')->update(['status' => 'cancelled']);
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'confirmed', 'delivering', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending'");
    }
};
