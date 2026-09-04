<?php
// Dev-server router for Akaunting (single `php -S` process).
//
// Akaunting links assets as asset('public/css/app.css')   => /public/css/app.css
//                            and asset('vendor/...')        => /vendor/...
// but the real files live in ./public and ./vendor respectively, while the
// framework front controller is ./public/index.php.
//
// This router serves any request whose path maps to a real file under
// ./public (including the /public/* alias) or ./vendor as a static file,
// and routes everything else through public/index.php.

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$publicDir = __DIR__ . '/public';
$vendorDir = __DIR__ . '/vendor';

// 1) /public/* alias -> ./public/* (Akaunting's asset() helper links these)
if (strpos($uri, '/public/') === 0) {
    $file = $publicDir . substr($uri, strlen('/public'));
    if (is_file($file)) {
        return false; // built-in server serves the static file
    }
}

// 2) /vendor/* -> ./vendor/* (Livewire, Alpine, etc.)
if (strpos($uri, '/vendor/') === 0) {
    $file = $vendorDir . substr($uri, strlen('/vendor'));
    if (is_file($file)) {
        return false;
    }
}

// 3) Other static assets that exist directly under ./public/
if ($uri !== '/' && is_file($publicDir . $uri)) {
    return false;
}

// 4) Everything else -> Laravel front controller
require $publicDir . '/index.php';
