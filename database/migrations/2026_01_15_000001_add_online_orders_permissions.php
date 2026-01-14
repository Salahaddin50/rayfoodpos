<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        $parent = Permission::firstOrCreate(
            ['name' => 'online-orders', 'guard_name' => 'sanctum'],
            ['title' => 'Online Orders', 'url' => 'online-orders', 'parent' => 0]
        );

        $children = [
            ['title' => 'Online Orders Show', 'name' => 'online_orders_show', 'url' => 'online-orders/show'],
            ['title' => 'Online Orders Delete', 'name' => 'online_orders_delete', 'url' => 'online-orders/delete'],
        ];

        foreach ($children as $child) {
            Permission::firstOrCreate(
                ['name' => $child['name'], 'guard_name' => 'sanctum'],
                [
                    'title'  => $child['title'],
                    'url'    => $child['url'],
                    'parent' => $parent->id,
                ]
            );
        }
    }

    public function down(): void
    {
        // Don't drop automatically; permissions are shared config data and may be used by existing roles.
    }
};

