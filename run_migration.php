<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Run the specific migration
$exitCode = $kernel->call('migrate', [
    '--path' => 'database/migrations/2026_01_09_000000_update_takeaway_types_menu_icon.php'
]);

exit($exitCode);



