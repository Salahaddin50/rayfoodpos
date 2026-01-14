<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // Ensure Online Orders exists and is placed under "Pos & Orders" (parent id = 5)
        // with a translation key (language) so it doesn't render as Menu.Null/menu.null.
        DB::table('menus')->updateOrInsert(
            ['url' => 'online-orders'],
            [
                'name'       => 'Online Orders',
                'language'   => 'online_orders',
                'icon'       => 'lab lab-line-bag-2',
                'status'     => 1,
                'parent'     => 5,
                'type'       => 1,
                'priority'   => 103,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        // Stabilize ordering inside the "Pos & Orders" group:
        // POS -> POS Orders -> Table Orders -> Online Orders -> KDS -> OSS
        $updates = [
            ['url' => 'pos',                    'priority' => 100, 'parent' => 5, 'status' => 1],
            ['url' => 'pos-orders',             'priority' => 101, 'parent' => 5, 'status' => 1],
            ['url' => 'table-orders',           'priority' => 102, 'parent' => 5, 'status' => 1],
            ['url' => 'online-orders',          'priority' => 103, 'parent' => 5, 'status' => 1],
            ['url' => 'kitchen-display-system', 'priority' => 104, 'parent' => 5, 'status' => 1],
            ['url' => 'order-status-screen',    'priority' => 105, 'parent' => 5, 'status' => 1],
        ];

        foreach ($updates as $u) {
            DB::table('menus')
                ->where('url', $u['url'])
                ->update([
                    'priority'   => $u['priority'],
                    'parent'     => $u['parent'],
                    'status'     => $u['status'],
                    'updated_at' => $now,
                ]);
        }
    }

    public function down(): void
    {
        // No-op: menu configuration is shared and may be customized per environment.
    }
};


