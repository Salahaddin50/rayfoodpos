<?php
/**
 * Quick log viewer - access at: https://rayyanscorner.az/view-logs.php
 * DELETE THIS FILE AFTER DEBUGGING!
 */

$logFile = __DIR__ . '/../storage/logs/laravel.log';

if (!file_exists($logFile)) {
    die('Log file not found');
}

$lines = file($logFile);
$lastLines = array_slice($lines, -200); // Last 200 lines

header('Content-Type: text/plain');
echo "=== LAST 200 LINES OF LARAVEL LOG ===\n\n";
echo implode('', $lastLines);
