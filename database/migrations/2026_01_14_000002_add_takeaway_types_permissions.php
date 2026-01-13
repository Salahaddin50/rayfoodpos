<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ensure Takeaway Types permission group exists so it can be configured in Settings -> Role & Permissions.
        // Parent permission uses dash naming (consistent with other parent pages like "dining-tables").
        $parent = Permission::firstOrCreate(
            ['name' => 'takeaway-types', 'guard_name' => 'sanctum'],
            ['title' => 'Takeaway Types', 'url' => 'takeaway-types', 'parent' => 0]
        );

        // Child permissions (CRUD) use underscore naming (consistent with existing controllers/middlewares).
        $children = [
            ['title' => 'Takeaway Types Create', 'name' => 'takeaway_types_create', 'url' => 'takeaway-types/create'],
            ['title' => 'Takeaway Types Edit', 'name' => 'takeaway_types_edit', 'url' => 'takeaway-types/edit'],
            ['title' => 'Takeaway Types Delete', 'name' => 'takeaway_types_delete', 'url' => 'takeaway-types/delete'],
            ['title' => 'Takeaway Types Show', 'name' => 'takeaway_types_show', 'url' => 'takeaway-types/show'],
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't drop automatically; permissions are shared config data and may be used by existing roles.
    }
};


