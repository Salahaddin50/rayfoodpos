<?php

/**
 * Laravel front controller for PHP's built-in web server.
 *
 * IMPORTANT:
 * Run with:
 *   php -S 127.0.0.1:8000 -t public server.php
 *
 * This ensures routes like /manifest.json are handled by Laravel, instead of 404'ing
 * when the file doesn't exist in /public.
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');

if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
    return false;
}

require_once __DIR__ . '/public/index.php';

