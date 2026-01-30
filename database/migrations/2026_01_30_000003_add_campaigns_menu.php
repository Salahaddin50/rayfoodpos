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
        // Find the Promo menu
        $promoMenu = DB::table('menus')->where('language', 'promo')->first();
        
        if ($promoMenu) {
            // Check if Campaigns menu already exists
            $existingCampaigns = DB::table('menus')->where('language', 'campaigns')->first();
            
            if (!$existingCampaigns) {
                DB::table('menus')->insert([
                    'name'       => 'Campaigns',
                    'language'   => 'campaigns',
                    'url'        => 'campaigns',
                    'icon'       => 'lab lab-campaign',
                    'priority'   => 100,
                    'status'     => 1,
                    'parent'     => $promoMenu->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('menus')->where('language', 'campaigns')->delete();
    }
};
