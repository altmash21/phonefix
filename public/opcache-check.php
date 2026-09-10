<?php
echo 'SAPI: ' . PHP_SAPI . '<br>';

if (function_exists('opcache_get_status')) {
    $status = @opcache_get_status();
    echo 'OPcache enabled: ' . ($status !== false ? 'YES' : 'NO') . '<br>';

    if ($status !== false && isset($status['opcache_statistics'])) {
        echo 'Cached scripts: ' . ($status['opcache_statistics']['num_cached_scripts'] ?? 0) . '<br>';
        echo 'Hits: ' . ($status['opcache_statistics']['hits'] ?? 0) . '<br>';
        echo 'Misses: ' . ($status['opcache_statistics']['misses'] ?? 0) . '<br>';
        if (isset($status['memory_usage'])) {
            $used = round($status['memory_usage']['used_memory'] / 1024 / 1024, 2);
            $free = round($status['memory_usage']['free_memory'] / 1024 / 1024, 2);
            echo "Memory: {$used} MB used / {$free} MB free<br>";
        }
    }
} else {
    echo 'OPcache enabled: NO (OPcache extension not installed/enabled in php.ini)<br>';
}
