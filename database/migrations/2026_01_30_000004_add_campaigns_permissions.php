<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $permissions = [
            [
                'title'      => 'Campaigns',
                'name'       => 'campaigns',
                'guard_name' => 'sanctum',
                'url'        => 'campaigns',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'Campaigns Create',
                'name'       => 'campaigns_create',
                'guard_name' => 'sanctum',
                'url'        => 'campaigns/create',
                'parent_id'  => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'Campaigns Edit',
                'name'       => 'campaigns_edit',
                'guard_name' => 'sanctum',
                'url'        => 'campaigns/edit',
                'parent_id'  => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'Campaigns Delete',
                'name'       => 'campaigns_delete',
                'guard_name' => 'sanctum',
                'url'        => 'campaigns/delete',
                'parent_id'  => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'Campaigns Show',
                'name'       => 'campaigns_show',
                'guard_name' => 'sanctum',
                'url'        => 'campaigns/show',
                'parent_id'  => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Create main permission first
        $mainPermission = Permission::where('name', 'campaigns')->first();
        if (!$mainPermission) {
            $mainPermission = Permission::create($permissions[0]);
        }

        // Create child permissions with parent_id
        for ($i = 1; $i < count($permissions); $i++) {
            $existingPermission = Permission::where('name', $permissions[$i]['name'])->first();
            if (!$existingPermission) {
                $permissions[$i]['parent_id'] = $mainPermission->id;
                Permission::create($permissions[$i]);
            }
        }

        // Assign permissions to admin role
        $adminRole = \Spatie\Permission\Models\Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $permissionNames = array_column($permissions, 'name');
            $adminRole->givePermissionTo($permissionNames);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $permissionNames = [
            'campaigns',
            'campaigns_create',
            'campaigns_edit',
            'campaigns_delete',
            'campaigns_show',
        ];

        foreach ($permissionNames as $name) {
            Permission::where('name', $name)->delete();
        }
    }
};
