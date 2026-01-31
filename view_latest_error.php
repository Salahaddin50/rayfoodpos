<?php
/**
 * View Latest Laravel Error
 * Upload and run: php view_latest_error.php
 */

$logFile = __DIR__ . '/storage/logs/laravel.log';

if (!file_exists($logFile)) {
    echo "Log file not found: $logFile\n";
    exit(1);
}

echo "=== Latest Laravel Errors (Last 200 lines) ===\n\n";

$lines = file($logFile);
$lastLines = array_slice($lines, -200);

foreach ($lastLines as $line) {
    echo $line;
}
