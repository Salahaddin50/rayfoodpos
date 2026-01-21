<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            // Monetary threshold for free delivery (subtotal threshold)
            $table->decimal('free_delivery_threshold', 12, 2)->nullable()->after('status');

            // Distance thresholds (km)
            $table->decimal('delivery_distance_threshold_1', 12, 2)->nullable()->after('free_delivery_threshold');
            $table->decimal('delivery_distance_threshold_2', 12, 2)->nullable()->after('delivery_distance_threshold_1');

            // Delivery costs for tiers
            $table->decimal('delivery_cost_1', 12, 2)->nullable()->after('delivery_distance_threshold_2');
            $table->decimal('delivery_cost_2', 12, 2)->nullable()->after('delivery_cost_1');
            $table->decimal('delivery_cost_3', 12, 2)->nullable()->after('delivery_cost_2');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn([
                'free_delivery_threshold',
                'delivery_distance_threshold_1',
                'delivery_distance_threshold_2',
                'delivery_cost_1',
                'delivery_cost_2',
                'delivery_cost_3',
            ]);
        });
    }
};

