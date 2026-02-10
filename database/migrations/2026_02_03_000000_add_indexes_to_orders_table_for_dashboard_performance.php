<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Add index on order_datetime for faster date range queries
            $table->index('order_datetime', 'orders_order_datetime_index');
            
            // Add composite index for payment_status + order_datetime queries (used in salesSummary)
            $table->index(['payment_status', 'order_datetime'], 'orders_payment_status_order_datetime_index');
            
            // Add composite index for status + order_datetime queries (used in totalOrders)
            $table->index(['status', 'order_datetime'], 'orders_status_order_datetime_index');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_order_datetime_index');
            $table->dropIndex('orders_payment_status_order_datetime_index');
            $table->dropIndex('orders_status_order_datetime_index');
        });
    }
};
