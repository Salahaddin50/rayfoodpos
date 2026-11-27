<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Speeds up dashboard counts, sidebar counts, and order list date filters
            $table->index(['branch_id', 'order_datetime'], 'orders_branch_datetime_idx');
            $table->index(['branch_id', 'source', 'order_datetime'], 'orders_branch_source_datetime_idx');
            $table->index(['branch_id', 'order_type', 'source', 'order_datetime'], 'orders_branch_type_source_datetime_idx');
            $table->index(['payment_status', 'order_datetime'], 'orders_payment_datetime_idx');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_branch_datetime_idx');
            $table->dropIndex('orders_branch_source_datetime_idx');
            $table->dropIndex('orders_branch_type_source_datetime_idx');
            $table->dropIndex('orders_payment_datetime_idx');
        });
    }
};
