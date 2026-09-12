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
    $file = public_path($path);

    if (file_exists($file) && !is_dir($file)) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if ($ext === 'php') {
            require $file;
            exit;
        }

        $mime = match($ext) {
            'css' => 'text/css; charset=utf-8',
            'js' => 'application/javascript; charset=utf-8',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'svg' => 'image/svg+xml',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject',
            'json' => 'application/json',
            default => function_exists('mime_content_type') && @mime_content_type($file) ? mime_content_type($file) : 'application/octet-stream',
        };

        return response()->file($file, [
            'Content-Type'  => $mime,
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

