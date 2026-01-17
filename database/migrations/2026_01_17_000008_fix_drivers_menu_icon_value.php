<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Some installs already have drivers icon set to a non-existing class "lab-delivery-boy".
        // Replace it with the correct icon class "lab-delivery-boys".
        DB::table('menus')
            ->where('url', 'drivers')
            ->where(function ($q) {
                $q->where('icon', 'lab lab-delivery-boy')
                  ->orWhere('icon', 'lab-delivery-boy')
                  ->orWhere('icon', 'lab lab-delivery-boy ');
            })
            ->update(['icon' => 'lab lab-delivery-boys']);
    }

    public function down(): void
    {
        // no-op
    }
};


