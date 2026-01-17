<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        $usersMenu = DB::table('menus')
            ->where('parent', 0)
            ->where('language', 'users')
            ->first();

        if (!$usersMenu) {
            return;
        }

        $now = now();

        $children = [
            [
                'name'       => 'Online Users',
                'language'   => 'online_users',
                'url'        => 'online-users',
                'icon'       => 'lab lab-customers',
                'priority'   => 100,
                'status'     => 1,
                'parent'     => $usersMenu->id,
                'type'       => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Drivers',
                'language'   => 'drivers',
                'url'        => 'drivers',
                'icon'       => 'lab lab-delivery-boy',
                'priority'   => 100,
                'status'     => 1,
                'parent'     => $usersMenu->id,
                'type'       => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($children as $row) {
            $exists = DB::table('menus')
                ->where('parent', $usersMenu->id)
                ->where('url', $row['url'])
                ->exists();

            if (!$exists) {
                DB::table('menus')->insert($row);
            }
        }
    }

    public function down()
    {
        DB::table('menus')->whereIn('url', ['online-users', 'drivers'])->delete();
    }
};


