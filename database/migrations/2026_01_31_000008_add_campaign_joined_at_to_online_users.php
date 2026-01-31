<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('online_users', function (Blueprint $table) {
            // Track when user joined their current campaign
            $table->timestamp('campaign_joined_at')->nullable()->after('campaign_id');
            
            // Index for efficient campaign join date queries
            $table->index(['campaign_id', 'campaign_joined_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('online_users', function (Blueprint $table) {
            $table->dropIndex(['campaign_id', 'campaign_joined_at']);
            $table->dropColumn('campaign_joined_at');
        });
    }
};
