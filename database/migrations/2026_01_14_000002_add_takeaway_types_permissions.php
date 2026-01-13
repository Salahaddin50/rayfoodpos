<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        // Create Takeaway Types permissions if missing so it appears in Settings -> Role & Permissions.
        $guard = 'sanctum';

        $ensurePermission = function (array $row): int {
            $existingId = DB::table('permissions')
                ->where('name', $row['name'])
                ->where('guard_name', $row['guard_name'])
                ->value('id');

            if ($existingId) {
                return (int) $existingId;
            }

            DB::table('permissions')->insert([
                'title' => $row['title'] ?? null,
                'name' => $row['name'],
                'guard_name' => $row['guard_name'],
                'url' => $row['url'] ?? null,
                'parent' => $row['parent'] ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return (int) DB::table('permissions')
                ->where('name', $row['name'])
                ->where('guard_name', $row['guard_name'])
                ->value('id');
        };

        $parentId = $ensurePermission([
            'title' => 'Takeaway Types',
            'name' => 'takeaway-types',
            'guard_name' => $guard,
            'url' => 'takeaway-types',
            'parent' => 0,
        ]);

        $childNames = [
            ['title' => 'Takeaway Types Create', 'name' => 'takeaway_types_create', 'url' => 'takeaway-types/create'],
            ['title' => 'Takeaway Types Edit', 'name' => 'takeaway_types_edit', 'url' => 'takeaway-types/edit'],
            ['title' => 'Takeaway Types Delete', 'name' => 'takeaway_types_delete', 'url' => 'takeaway-types/delete'],
            ['title' => 'Takeaway Types Show', 'name' => 'takeaway_types_show', 'url' => 'takeaway-types/show'],
        ];

        foreach ($childNames as $child) {
            $ensurePermission([
                'title' => $child['title'],
                'name' => $child['name'],
                'guard_name' => $guard,
                'url' => $child['url'],
                'parent' => $parentId,
            ]);
        }

        // Ensure Admin has access to the new permission by default (if roles table exists).
        try {
            $admin = Role::find(1);
            if ($admin) {
                $admin->givePermissionTo([
                    'takeaway-types',
                    'takeaway_types_create',
                    'takeaway_types_edit',
                    'takeaway_types_delete',
                    'takeaway_types_show',
                ]);
            }
        } catch (\Throwable $e) {
            // Ignore if role/permission system isn't ready during migration in some environments.
        }
    }

    public function down(): void
    {
        $guard = 'sanctum';
        $names = [
            'takeaway-types',
            'takeaway_types_create',
            'takeaway_types_edit',
            'takeaway_types_delete',
            'takeaway_types_show',
        ];

        DB::table('permissions')->whereIn('name', $names)->where('guard_name', $guard)->delete();
    }
};


