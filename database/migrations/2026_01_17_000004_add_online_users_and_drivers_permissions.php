<?php

use App\Enums\Role as EnumRole;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        // Create permissions (if missing)
        $onlineUsers = Permission::firstOrCreate(
            ['name' => 'online_users', 'guard_name' => 'sanctum'],
            ['title' => 'Online Users', 'url' => 'online-users', 'parent' => 0]
        );

        $drivers = Permission::firstOrCreate(
            ['name' => 'drivers', 'guard_name' => 'sanctum'],
            ['title' => 'Drivers', 'url' => 'drivers', 'parent' => 0]
        );

        $driverChildren = [
            ['title' => 'Drivers Create', 'name' => 'drivers_create', 'url' => 'drivers/create'],
            ['title' => 'Drivers Edit',   'name' => 'drivers_edit',   'url' => 'drivers/edit'],
            ['title' => 'Drivers Delete', 'name' => 'drivers_delete', 'url' => 'drivers/delete'],
        ];

        foreach ($driverChildren as $child) {
            Permission::firstOrCreate(
                ['name' => $child['name'], 'guard_name' => 'sanctum'],
                [
                    'title'  => $child['title'],
                    'url'    => $child['url'],
                    'parent' => $drivers->id,
                ]
            );
        }

        // Assign to Admin role (existing installs won't re-run RolePermissionTableSeeder)
        $admin = Role::find(EnumRole::ADMIN);
        if ($admin) {
            $toAssign = Permission::whereIn('name', [
                'online_users',
                'drivers',
                'drivers_create',
                'drivers_edit',
                'drivers_delete',
            ])->get();
            $admin->givePermissionTo($toAssign);
        }
    }

    public function down(): void
    {
        // Don't drop automatically; permissions are shared config data and may be used by existing roles.
    }
};


