<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('online_users', function (Blueprint $table) {
            // Manual order count override - allows admin to directly set order count (e.g., 5/8)
            // When set, this value is used instead of calculating from orders
            $table->unsignedInteger('campaign_manual_order_count')->nullable()->after('campaign_joined_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('online_users', function (Blueprint $table) {
            $table->dropColumn('campaign_manual_order_count');
        });
    }
};
