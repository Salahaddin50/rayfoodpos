<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure Drivers menu icon is not blank for existing installs where the row already exists.
        DB::table('menus')
            ->where('url', 'drivers')
            ->where(function ($q) {
                $q->whereNull('icon')
                  ->orWhere('icon', '')
                  ->orWhere('icon', 'lab')
                  ->orWhere('icon', 'lab ');
            })
            ->update(['icon' => 'lab lab-delivery-boy']);
    }

    public function down(): void
    {
        // no-op
    }
};


