<?php

use App\Enums\Role as EnumRole;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $parent = Permission::firstOrCreate(
            ['name' => 'online_users', 'guard_name' => 'sanctum'],
            ['title' => 'Online Users', 'url' => 'online-users', 'parent' => 0]
        );

        $children = [
            ['title' => 'Online Users Create', 'name' => 'online_users_create', 'url' => 'online-users/create'],
            ['title' => 'Online Users Edit',   'name' => 'online_users_edit',   'url' => 'online-users/edit'],
            ['title' => 'Online Users Delete', 'name' => 'online_users_delete', 'url' => 'online-users/delete'],
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

        $admin = Role::find(EnumRole::ADMIN);
        if ($admin) {
            $toAssign = Permission::whereIn('name', [
                'online_users',
                'online_users_create',
                'online_users_edit',
                'online_users_delete',
            ])->get();
            $admin->givePermissionTo($toAssign);
        }
    }

    public function down(): void
    {
        // Don't drop automatically; permissions are shared config data.
    }
};


