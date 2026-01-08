<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
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
        // Update the takeaway_types menu icon
        // Using lab lab-bag-2 (valid icon class from lab.css)
        DB::table('menus')
            ->where('language', 'takeaway_types')
            ->where('url', 'takeaway-types')
            ->update([
                'icon' => 'lab lab-bag-2',
                'updated_at' => now()
            ]);
        
        // Also ensure any menu with takeaway_types language has the icon
        DB::table('menus')
            ->where('language', 'takeaway_types')
            ->where(function($query) {
                $query->whereNull('icon')
                      ->orWhere('icon', '')
                      ->orWhere('icon', ' ');
            })
            ->update([
                'icon' => 'lab lab-bag-2',
                'updated_at' => now()
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert to empty icon (or previous icon if needed)
        DB::table('menus')
            ->where('language', 'takeaway_types')
            ->where('url', 'takeaway-types')
            ->update([
                'icon' => '',
                'updated_at' => now()
            ]);
    }
};

