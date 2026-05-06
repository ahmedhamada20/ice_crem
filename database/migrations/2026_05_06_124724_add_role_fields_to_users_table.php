<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->foreignId('zone_id')->nullable()->after('phone')->constrained('zones')->nullOnDelete();
            $table->enum('status', ['active', 'inactive'])->default('active')->after('zone_id');
            $table->string('avatar')->nullable()->after('status');
            $table->softDeletes();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['zone_id']);
            $table->dropColumn(['phone', 'zone_id', 'status', 'avatar', 'deleted_at']);
        });
    }
};
