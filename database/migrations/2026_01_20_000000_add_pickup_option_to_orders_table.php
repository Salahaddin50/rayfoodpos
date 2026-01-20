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
        if (!Schema::hasColumn('orders', 'pickup_option')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('pickup_option')->nullable()->after('delivery_charge');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('orders', 'pickup_option')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('pickup_option');
            });
        }
    }
};
