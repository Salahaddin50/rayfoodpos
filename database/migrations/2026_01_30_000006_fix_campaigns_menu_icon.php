<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Update campaigns menu icon to use lab lab-offers (same as Offers)
        DB::table('menus')
            ->where('language', 'campaigns')
            ->update(['icon' => 'lab lab-offers']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('menus')
            ->where('language', 'campaigns')
            ->update(['icon' => 'lab lab-campaign']);
    }
};
