<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'campaign_id')) {
                $table->unsignedBigInteger('campaign_id')->nullable()->after('location_url');
                $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('set null');
            }

            if (!Schema::hasColumn('orders', 'campaign_discount')) {
                $table->decimal('campaign_discount', 19, 6)->default(0)->after('campaign_id');
            }

            if (!Schema::hasColumn('orders', 'campaign_redeem_free_item_id')) {
                $table->unsignedBigInteger('campaign_redeem_free_item_id')->nullable()->after('campaign_discount');
            }

            if (!Schema::hasColumn('orders', 'campaign_snapshot')) {
                $table->json('campaign_snapshot')->nullable()->after('campaign_redeem_free_item_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'campaign_id')) {
                $table->dropForeign(['campaign_id']);
                $table->dropColumn('campaign_id');
            }
            if (Schema::hasColumn('orders', 'campaign_discount')) {
                $table->dropColumn('campaign_discount');
            }
            if (Schema::hasColumn('orders', 'campaign_redeem_free_item_id')) {
                $table->dropColumn('campaign_redeem_free_item_id');
            }
            if (Schema::hasColumn('orders', 'campaign_snapshot')) {
                $table->dropColumn('campaign_snapshot');
            }
        });
    }
};

