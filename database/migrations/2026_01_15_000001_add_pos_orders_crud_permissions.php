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
        // Ensure POS-related parent permissions have CRUD child permissions so
        // Settings -> Role & Permissions renders Create/Update/Delete/View checkboxes.
        //
        // Parent permissions use dash naming (e.g. "pos-orders").
        // Child permissions use underscore naming (e.g. "pos_orders_create").
        $parents = [
            ['title' => 'POS',        'name' => 'pos',                   'url' => 'pos'],
            ['title' => 'POS Orders', 'name' => 'pos-orders',            'url' => 'pos-orders'],
            ['title' => 'Table Orders','name' => 'table-orders',         'url' => 'table-orders'],
            ['title' => 'K.D.S',      'name' => 'kitchen-display-system', 'url' => 'kitchen-display-system'],
            ['title' => 'O.S.S',      'name' => 'order-status-screen',    'url' => 'order-status-screen'],
        ];

        $actions = [
            ['label' => 'Create', 'suffix' => 'create', 'path' => 'create'],
            ['label' => 'Edit',   'suffix' => 'edit',   'path' => 'edit'],   // UI header says "Update"
            ['label' => 'Delete', 'suffix' => 'delete', 'path' => 'delete'],
            ['label' => 'Show',   'suffix' => 'show',   'path' => 'show'],   // UI header says "View"
        ];

        foreach ($parents as $p) {
            $parent = Permission::firstOrCreate(
                ['name' => $p['name'], 'guard_name' => 'sanctum'],
                ['title' => $p['title'], 'url' => $p['url'], 'parent' => 0]
            );

            $base = str_replace('-', '_', $parent->name);

            foreach ($actions as $a) {
                Permission::firstOrCreate(
                    ['name' => "{$base}_{$a['suffix']}", 'guard_name' => 'sanctum'],
                    [
                        'title'  => "{$parent->title} {$a['label']}",
                        'url'    => $parent->url ? "{$parent->url}/{$a['path']}" : null,
                        'parent' => $parent->id,
                    ]
                );
            }
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


