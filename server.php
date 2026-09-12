<?php

/**
 * Laravel - Development Server Router Script
 *
 * This server script emulates Apache's mod_rewrite and serves static assets
 * correctly when running via `php artisan serve`.
 */

$publicPath = getcwd();

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// Normalize company-scoped paths: e.g. /1/public/* or /1/css/* -> /public/* or /css/*
$assetUri = preg_replace('#^/[0-9]+/#', '/', $uri);

// Strip /public/ prefix since $publicPath is already the public directory
if (str_starts_with($assetUri, '/public/')) {
    $assetUri = substr($assetUri, 7);
}

$file = $publicPath . $assetUri;

// If the static file exists directly under public directory
if ($assetUri !== '/' && is_file($file)) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    if ($ext === 'php') {
        require $file;
        exit;
    }

    $mimes = [
        'css'   => 'text/css; charset=utf-8',
        'js'    => 'application/javascript; charset=utf-8',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'svg'   => 'image/svg+xml',
        'ico'   => 'image/x-icon',
        'webp'  => 'image/webp',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
        'eot'   => 'application/vnd.ms-fontobject',
        'json'  => 'application/json',
    ];

    header('Content-Type: ' . ($mimes[$ext] ?? 'application/octet-stream'));
    header('Cache-Control: public, max-age=86400');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
}

// Route all dynamic requests through Laravel front controller
require_once $publicPath . '/index.php';
