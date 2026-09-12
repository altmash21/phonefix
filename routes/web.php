<?php

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

/**
 * 'web' middleware applied to all routes
 *
 * @see \App\Providers\Route::mapWebRoutes
 */

Livewire::setScriptRoute(function ($handle) {
    $base = request()->getBasePath();

    return Route::get($base . '/vendor/livewire/livewire/dist/livewire.min.js', $handle);
});

Route::get('diagnostics', function () {
    require public_path('diagnostics/index.php');
    exit;
});

$serveStaticAsset = function ($path) {
    // Normalize path by trimming leading slashes
    $path = ltrim($path, '/');
    if (str_starts_with($path, 'public/')) {
        $path = substr($path, 7);
    }

    $file = public_path($path);

    if (file_exists($file) && !is_dir($file)) {
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

        return response()->file($file, [
            'Content-Type'  => $mimes[$ext] ?? (function_exists('mime_content_type') && @mime_content_type($file) ? mime_content_type($file) : 'application/octet-stream'),
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    abort(404);
};

// Root asset routes
Route::get('public/{path}', function ($path) use ($serveStaticAsset) {
    return $serveStaticAsset($path);
})->where('path', '.*');

Route::get('{folder}/{path}', function ($folder, $path) use ($serveStaticAsset) {
    return $serveStaticAsset($folder . '/' . $path);
})->where('folder', 'css|js|vendor|img|fonts')->where('path', '.*');

// Company-prefixed asset routes (e.g. /{company_id}/public/*, /{company_id}/css/*)
Route::get('{company_id}/public/{path}', function ($company_id, $path) use ($serveStaticAsset) {
    return $serveStaticAsset($path);
})->where('company_id', '[0-9]+')->where('path', '.*');

Route::get('{company_id}/{folder}/{path}', function ($company_id, $folder, $path) use ($serveStaticAsset) {
    return $serveStaticAsset($folder . '/' . $path);
})->where('company_id', '[0-9]+')->where('folder', 'css|js|vendor|img|fonts')->where('path', '.*');


