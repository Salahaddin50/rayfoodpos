<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "Updating Takeaway Types menu icon...\n";
    
    // Update the takeaway_types menu icon
    $updated = DB::table('menus')
        ->where('language', 'takeaway_types')
        ->where('url', 'takeaway-types')
        ->update([
            'icon' => 'lab lab-bag-line',
            'updated_at' => now()
        ]);
    
    // Also ensure any menu with takeaway_types language has the icon
    $updated2 = DB::table('menus')
        ->where('language', 'takeaway_types')
        ->where(function($query) {
            $query->whereNull('icon')
                  ->orWhere('icon', '')
                  ->orWhere('icon', ' ');
        })
        ->update([
            'icon' => 'lab lab-bag-line',
            'updated_at' => now()
        ]);
    
    if ($updated > 0 || $updated2 > 0) {
        echo "✅ Successfully updated menu icon!\n";
        echo "Updated {$updated} menu entry(ies) by URL\n";
        echo "Updated {$updated2} menu entry(ies) by empty icon\n";
    } else {
        echo "⚠️  No menu entries found to update. The icon might already be set.\n";
    }
    
    echo "\n✅ Done! Please refresh your browser (Ctrl + Shift + R) to see the icon.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}



