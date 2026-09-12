<?php
// Dev-server router for Akaunting (single `php -S` process).
//
// Akaunting links assets as asset('public/css/app.css')   => /public/css/app.css
//                            and asset('vendor/...')        => /vendor/...
// but the real files live in ./public and ./vendor respectively, while the
// framework front controller is ./public/index.php.
//
// This router serves any request whose path maps to a real file under
// ./public (including the /public/* alias and company-prefixed assets)
// or ./vendor as a static file with explicit MIME headers,
// and routes everything else through public/index.php.

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$publicDir = __DIR__ . '/public';
$vendorDir = __DIR__ . '/vendor';

// Normalize company-scoped paths: e.g. /1/public/* or /1/css/* -> /public/* or /css/*
$normalizedUri = preg_replace('#^/[0-9]+/#', '/', $uri);

$targetFile = null;

// 1) /public/* alias -> ./public/* (Akaunting's asset() helper links these)
if (str_starts_with($normalizedUri, '/public/')) {
    $sub = substr($normalizedUri, strlen('/public'));
    if (is_file($publicDir . $sub)) {
        $targetFile = $publicDir . $sub;
    }
}

// 2) /vendor/* -> ./vendor/* (Livewire, Alpine, etc.)
if (!$targetFile && str_starts_with($normalizedUri, '/vendor/')) {
    $sub = substr($normalizedUri, strlen('/vendor'));
    if (is_file($vendorDir . $sub)) {
        $targetFile = $vendorDir . $sub;
    } elseif (is_file($publicDir . $normalizedUri)) {
        $targetFile = $publicDir . $normalizedUri;
    }
}

// 3) Other static assets directly under ./public/ (e.g. /css/..., /js/..., /img/..., /fonts/...)
if (!$targetFile && $normalizedUri !== '/' && is_file($publicDir . $normalizedUri)) {
    $targetFile = $publicDir . $normalizedUri;
}

// If a static file was resolved, stream it with explicit MIME types
if ($targetFile && is_file($targetFile)) {
    $ext = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    if ($ext === 'php') {
        require $targetFile;
        exit;
    }

    $mimes = [
        'css'   => 'text/css; charset=UTF-8',
        'js'    => 'application/javascript; charset=UTF-8',
        'json'  => 'application/json',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'svg'   => 'image/svg+xml',
        'webp'  => 'image/webp',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
        'eot'   => 'application/vnd.ms-fontobject',
        'ico'   => 'image/x-icon',
    ];

    header('Content-Type: ' . ($mimes[$ext] ?? 'application/octet-stream'));
    header('Cache-Control: public, max-age=86400');
    header('Content-Length: ' . filesize($targetFile));
    readfile($targetFile);
    exit;
}

// 4) Everything else -> Laravel front controller
require $publicDir . '/index.php';
