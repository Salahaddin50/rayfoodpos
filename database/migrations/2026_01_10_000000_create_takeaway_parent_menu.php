<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Enums\MenuType;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Find the existing Takeaway Types menu
        $takeawayTypesMenu = DB::table('menus')
            ->where('language', 'takeaway_types')
            ->where('url', 'takeaway-types')
            ->first();

        if ($takeawayTypesMenu) {
            // Create parent "Takeaway" menu
            $parentId = DB::table('menus')->insertGetId([
                'name' => 'Takeaway',
                'language' => 'takeaway',
                'url' => '#',
                'icon' => 'lab lab-bag-line',
                'status' => 1,
                'parent' => 0,
                'type' => MenuType::BACKEND,
                'priority' => 21,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Update Takeaway Types to be a child of the new parent
            DB::table('menus')
                ->where('id', $takeawayTypesMenu->id)
                ->update([
                    'parent' => $parentId,
                    'updated_at' => now()
                ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Find the parent Takeaway menu
        $parentMenu = DB::table('menus')
            ->where('language', 'takeaway')
            ->where('url', '#')
            ->first();

        if ($parentMenu) {
            // Update Takeaway Types back to standalone (parent = 0)
            DB::table('menus')
                ->where('parent', $parentMenu->id)
                ->where('language', 'takeaway_types')
                ->update([
                    'parent' => 0,
                    'updated_at' => now()
                ]);

            // Delete the parent menu
            DB::table('menus')->where('id', $parentMenu->id)->delete();
        }
    }
};

