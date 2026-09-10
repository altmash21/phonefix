<?php
/**
 * MobiTrack Performance & Connection Diagnostics Tool
 * Standalone diagnostic utility to measure connection stages, TTFB, server latency, and pinpoint bottlenecks.
 */

// 1. Server-Side Benchmarking
$serverBenchmarks = [];

// Raw PHP Compute Benchmark
$t0 = microtime(true);
$sum = 0;
for ($i = 0; $i < 100000; $i++) {
    $sum += $i;
}
$serverBenchmarks['php_compute_ms'] = round((microtime(true) - $t0) * 1000, 2);

// Disk I/O Benchmark (Laravel Session Directory)
$sessionDir = dirname(__DIR__, 2) . '/storage/framework/sessions';
$diskBench = ['write_ms' => null, 'read_ms' => null, 'writable' => false];
if (is_dir($sessionDir) && is_writable($sessionDir)) {
    $diskBench['writable'] = true;
    $testFile = $sessionDir . '/.perf_test_' . uniqid();
    $data = str_repeat('A', 8192); // 8KB mock session

    $t0 = microtime(true);
    @file_put_contents($testFile, $data);
    $diskBench['write_ms'] = round((microtime(true) - $t0) * 1000, 2);

    $t0 = microtime(true);
    @file_get_contents($testFile);
    $diskBench['read_ms'] = round((microtime(true) - $t0) * 1000, 2);

    @unlink($testFile);
}
$serverBenchmarks['disk_io'] = $diskBench;

// OPcache Status
$opcache = [
    'enabled' => false,
    'num_cached_scripts' => 0,
    'hits' => 0,
    'misses' => 0,
    'hit_rate' => 0,
    'used_memory_mb' => 0,
    'free_memory_mb' => 0,
];
if (function_exists('opcache_get_status')) {
    $status = @opcache_get_status(false);
    if ($status && is_array($status)) {
        $opcache['enabled'] = (bool) ($status['opcache_enabled'] ?? false);
        if (isset($status['opcache_statistics'])) {
            $opcache['num_cached_scripts'] = $status['opcache_statistics']['num_cached_scripts'] ?? 0;
            $opcache['hits'] = $status['opcache_statistics']['hits'] ?? 0;
            $opcache['misses'] = $status['opcache_statistics']['misses'] ?? 0;
            $total = $opcache['hits'] + $opcache['misses'];
            $opcache['hit_rate'] = $total > 0 ? round(($opcache['hits'] / $total) * 100, 1) : 0;
        }
        if (isset($status['memory_usage'])) {
            $opcache['used_memory_mb'] = round(($status['memory_usage']['used_memory'] ?? 0) / 1024 / 1024, 1);
            $opcache['free_memory_mb'] = round(($status['memory_usage']['free_memory'] ?? 0) / 1024 / 1024, 1);
        }
    }
}
$serverBenchmarks['opcache'] = $opcache;

// Database Connection & Latency Benchmark
$dbBench = ['connected' => false, 'connect_ms' => null, 'query_ms' => null, 'error' => null];
$envPath = dirname(__DIR__, 2) . '/.env';
if (file_exists($envPath)) {
    $envContent = @file_get_contents($envPath);
    preg_match('/^DB_HOST=(.*)$/m', $envContent, $mHost);
    preg_match('/^DB_PORT=(.*)$/m', $envContent, $mPort);
    preg_match('/^DB_DATABASE=(.*)$/m', $envContent, $mDb);
    preg_match('/^DB_USERNAME=(.*)$/m', $envContent, $mUser);
    preg_match('/^DB_PASSWORD=(.*)$/m', $envContent, $mPass);

    $dbHost = trim($mHost[1] ?? '127.0.0.1', " \t\n\r\0\x0B\"'");
    $dbPort = (int) trim($mPort[1] ?? '3306', " \t\n\r\0\x0B\"'");
    $dbName = trim($mDb[1] ?? '', " \t\n\r\0\x0B\"'");
    $dbUser = trim($mUser[1] ?? 'root', " \t\n\r\0\x0B\"'");
    $dbPass = trim($mPass[1] ?? '', " \t\n\r\0\x0B\"'");

    if ($dbName && class_exists('PDO')) {
        $t0 = microtime(true);
        try {
            $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
            $pdo = new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_TIMEOUT => 2,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            $dbBench['connect_ms'] = round((microtime(true) - $t0) * 1000, 2);
            $dbBench['connected'] = true;

            $t1 = microtime(true);
            $stmt = $pdo->query("SELECT 1");
            $stmt->fetch();
            $dbBench['query_ms'] = round((microtime(true) - $t1) * 1000, 2);
        } catch (\Throwable $e) {
            $dbBench['error'] = $e->getMessage();
        }
    }
}
$serverBenchmarks['database'] = $dbBench;

// Environment Summary
$protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
$httpVersion = str_contains($protocol, '2.0') || str_contains($protocol, '2') ? 'HTTP/2' : (str_contains($protocol, '3') ? 'HTTP/3' : 'HTTP/1.1');
$serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? 'PHP CLI / Web Server';
$requestUri = $_SERVER['REQUEST_URI'] ?? '/diagnostics';
$phpSapi = PHP_SAPI;
$phpVersion = PHP_VERSION;
$memoryLimit = ini_get('memory_limit');
$maxExecutionTime = ini_get('max_execution_time') . 's';

// If requested via JSON API (for AJAX benchmarks)
if (isset($_GET['api'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'server' => $serverBenchmarks,
        'protocol' => $httpVersion,
        'sapi' => $phpSapi,
        'timestamp' => microtime(true),
    ]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MobiTrack Network & Server Diagnostics</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=JetBrains+Mono:wght@400;500;600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-canvas: #0b0f19;
            --bg-card: #131b2e;
            --bg-card-hover: #19243d;
            --border-card: #1e2c4a;
            --border-light: rgba(255, 255, 255, 0.08);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --accent: #6366f1;
            --accent-glow: rgba(99, 102, 241, 0.25);
            --fast: #10b981;
            --fast-bg: rgba(16, 185, 129, 0.12);
            --warn: #f59e0b;
            --warn-bg: rgba(245, 158, 11, 0.12);
            --bad: #ef4444;
            --bad-bg: rgba(239, 68, 68, 0.12);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: var(--bg-canvas);
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
            line-height: 1.5;
            min-height: 100vh;
            padding: 32px 20px;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
            flex-wrap: wrap;
            gap: 16px;
        }
        .header-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .header-title h1 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.02em;
        }
        .header-badge {
            font-size: 11px;
            font-weight: 600;
            background: var(--accent-glow);
            color: #818cf8;
            border: 1px solid rgba(99, 102, 241, 0.3);
            padding: 4px 10px;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .grid-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 4px 20px -2px rgba(0,0,0,0.4);
            transition: transform 0.15s ease, border-color 0.15s ease;
        }
        .card:hover {
            border-color: #2a3c63;
        }

        .metric-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 6px;
        }
        .metric-val {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 26px;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: baseline;
            gap: 6px;
        }
        .metric-sub {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 6px;
        }
        .badge-fast { background: var(--fast-bg); color: var(--fast); border: 1px solid rgba(16, 185, 129, 0.2); }
        .badge-warn { background: var(--warn-bg); color: var(--warn); border: 1px solid rgba(245, 158, 11, 0.2); }
        .badge-bad { background: var(--bad-bg); color: var(--bad); border: 1px solid rgba(239, 68, 68, 0.2); }

        /* Main Bottleneck Box */
        .bottleneck-hero {
            background: linear-gradient(135deg, #18233c 0%, #111a2e 100%);
            border: 1px solid #2d3e66;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
        }
        .bottleneck-hero::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, #ef4444, #f59e0b, #6366f1);
        }
        .hero-title-wrap {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 16px;
        }
        .hero-title {
            font-size: 18px;
            font-weight: 700;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #fff;
        }
        .hero-subtitle {
            font-size: 13px;
            color: var(--text-secondary);
            margin-top: 2px;
        }

        /* Waterfall Table */
        .stage-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
        }
        .stage-table th {
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            padding: 8px 12px;
            border-bottom: 1px solid var(--border-light);
        }
        .stage-table td {
            padding: 12px;
            border-bottom: 1px solid var(--border-light);
            font-size: 13px;
        }
        .stage-table tr:hover td {
            background: rgba(255, 255, 255, 0.02);
        }

        .bar-container {
            width: 100%;
            background: rgba(255, 255, 255, 0.05);
            height: 10px;
            border-radius: 5px;
            overflow: hidden;
            position: relative;
        }
        .bar-fill {
            height: 100%;
            border-radius: 5px;
            transition: width 0.4s ease;
        }

        .mono {
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
        }

        /* Interactive Tester */
        .tester-card {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 14px;
            padding: 22px;
            margin-bottom: 24px;
        }
        .tester-row {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 14px;
        }
        .url-input {
            flex: 1;
            min-width: 260px;
            background: #0d1322;
            border: 1px solid #233152;
            color: #fff;
            padding: 10px 14px;
            border-radius: 8px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
        }
        .url-input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 2px var(--accent-glow);
        }
        .btn-test {
            background: var(--accent);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.15s ease;
        }
        .btn-test:hover {
            background: #4f46e5;
        }
        .quick-links {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        .pill-link {
            font-size: 11px;
            color: var(--text-secondary);
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border-light);
            padding: 4px 10px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
        }
        .pill-link:hover {
            background: rgba(255, 255, 255, 0.09);
            color: #fff;
        }

        .fix-box {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.25);
            border-radius: 10px;
            padding: 16px;
            margin-top: 16px;
        }
        .fix-box.warn {
            background: rgba(245, 158, 11, 0.08);
            border-color: rgba(245, 158, 11, 0.25);
        }
        .fix-box.good {
            background: rgba(16, 185, 129, 0.08);
            border-color: rgba(16, 185, 129, 0.25);
        }
        .fix-title {
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .fix-desc {
            font-size: 13px;
            color: var(--text-secondary);
            line-height: 1.5;
        }
        .fix-list {
            margin-top: 10px;
            padding-left: 20px;
            font-size: 13px;
            color: var(--text-secondary);
        }
        .fix-list li { margin-bottom: 6px; }
        .fix-list code {
            background: #0d1322;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            color: #818cf8;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div class="header-title">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#818cf8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
            <h1>MobiTrack Performance Diagnostics</h1>
        </div>
        <span class="header-badge">Live Server & Network Analyzer</span>
    </div>

    <!-- Live Server Health Summary Cards -->
    <div class="grid-cards">
        <div class="card">
            <div class="metric-label">HTTP Protocol</div>
            <div class="metric-val">
                <?= htmlspecialchars($httpVersion) ?>
                <?php if ($httpVersion === 'HTTP/1.1'): ?>
                    <span class="badge-status badge-warn">Socket Limit: 6</span>
                <?php else: ?>
                    <span class="badge-status badge-fast">Multiplexed</span>
                <?php endif; ?>
            </div>
            <div class="metric-sub"><?= htmlspecialchars($serverSoftware) ?> (<?= htmlspecialchars($phpSapi) ?>)</div>
        </div>

        <div class="card">
            <div class="metric-label">PHP OPcache</div>
            <div class="metric-val">
                <?php if ($opcache['enabled']): ?>
                    <span style="color: var(--fast);">Active</span>
                    <span class="badge-status badge-fast"><?= $opcache['hit_rate'] ?>% Hits</span>
                <?php else: ?>
                    <span style="color: var(--bad);">Disabled</span>
                    <span class="badge-status badge-bad">High Overhead</span>
                <?php endif; ?>
            </div>
            <div class="metric-sub">
                <?php if ($opcache['enabled']): ?>
                    <?= $opcache['num_cached_scripts'] ?> scripts / <?= $opcache['used_memory_mb'] ?> MB used
                <?php else: ?>
                    PHP compiles Blade & classes on every hit
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="metric-label">Database Latency</div>
            <div class="metric-val">
                <?php if ($dbBench['connected']): ?>
                    <?= $dbBench['query_ms'] ?> <span style="font-size:14px;color:var(--text-secondary);">ms</span>
                    <?php if ($dbBench['query_ms'] < 5): ?>
                        <span class="badge-status badge-fast">Optimal</span>
                    <?php elseif ($dbBench['query_ms'] < 20): ?>
                        <span class="badge-status badge-warn">Moderate</span>
                    <?php else: ?>
                        <span class="badge-status badge-bad">High Latency</span>
                    <?php endif; ?>
                <?php else: ?>
                    <span style="color:var(--bad);">Error</span>
                <?php endif; ?>
            </div>
            <div class="metric-sub">
                Connect: <?= $dbBench['connect_ms'] ?? 'N/A' ?>ms | Query: <?= $dbBench['query_ms'] ?? 'N/A' ?>ms
            </div>
        </div>

        <div class="card">
            <div class="metric-label">Session Disk I/O</div>
            <div class="metric-val">
                <?= $diskBench['write_ms'] ?? '0' ?> <span style="font-size:14px;color:var(--text-secondary);">ms</span>
                <?php if (($diskBench['write_ms'] ?? 0) < 5): ?>
                    <span class="badge-status badge-fast">Fast Storage</span>
                <?php else: ?>
                    <span class="badge-status badge-warn">Disk Delay</span>
                <?php endif; ?>
            </div>
            <div class="metric-sub">
                Session write: <?= $diskBench['write_ms'] ?? 0 ?>ms | read: <?= $diskBench['read_ms'] ?? 0 ?>ms
            </div>
        </div>
    </div>

    <!-- URL Live Test Form -->
    <div class="tester-card">
        <div class="metric-label">Test Specific Admin Endpoint</div>
        <div class="tester-row">
            <input type="text" id="target-url" class="url-input" value="<?= htmlspecialchars($requestUri) ?>" placeholder="e.g. /1/mobileshop/dashboard">
            <button class="btn-test" id="btn-run-test" onclick="runAnalysis()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                Analyze Page Speed
            </button>
        </div>
        <div class="quick-links">
            <span style="font-size:11px;color:var(--text-muted);align-self:center;">Quick Select:</span>
            <span class="pill-link" onclick="setUrl('<?= htmlspecialchars($requestUri) ?>')">Current Diagnostics Page</span>
            <span class="pill-link" onclick="setUrl('/1/mobileshop/dashboard')">Dashboard (/1/mobileshop/dashboard)</span>
            <span class="pill-link" onclick="setUrl('/1/mobileshop/sales')">Sales Hub (/1/mobileshop/sales)</span>
            <span class="pill-link" onclick="setUrl('/1/mobileshop/pos')">POS (/1/mobileshop/pos)</span>
            <span class="pill-link" onclick="setUrl('/1/mobileshop/masters')">Masters (/1/mobileshop/masters)</span>
        </div>
    </div>

    <!-- Bottleneck Hero Diagnosis -->
    <div class="bottleneck-hero" id="bottleneck-section">
        <div class="hero-title-wrap">
            <div>
                <div class="hero-title" id="bottleneck-title">Analyzing Connection Lifecycle...</div>
                <div class="hero-subtitle" id="bottleneck-subtitle">Reading W3C Navigation & Resource Timing API metrics</div>
            </div>
            <div id="bottleneck-badge"></div>
        </div>

        <div id="bottleneck-explanation"></div>

        <!-- Waterfall Stages Breakdown Table -->
        <table class="stage-table" id="timing-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Connection Stage</th>
                    <th style="width: 15%;">Duration</th>
                    <th style="width: 15%;">% of Total</th>
                    <th style="width: 30%;">Visual Timeline</th>
                    <th style="width: 15%;">Evaluation</th>
                </tr>
            </thead>
            <tbody id="timing-tbody">
                <!-- Dynamically populated via JS -->
            </tbody>
        </table>
    </div>

    <!-- Recommendations & Root Causes -->
    <div class="card" id="recommendations-card">
        <h3 style="font-family:'Plus Jakarta Sans',sans-serif;font-size:16px;margin-bottom:12px;">Root Causes & Recommended Solutions</h3>
        <div id="recommendations-content">
            <!-- Dynamically populated based on bottleneck stage -->
        </div>
    </div>
</div>

<script>
function setUrl(url) {
    document.getElementById('target-url').value = url;
    runAnalysis();
}

function runAnalysis() {
    var url = document.getElementById('target-url').value.trim();
    if (!url) return;

    var btn = document.getElementById('btn-run-test');
    btn.disabled = true;
    btn.innerText = 'Analyzing...';

    // If testing the current page itself, use navigation timing
    if (url === window.location.pathname || url === window.location.href || url === '<?= htmlspecialchars($requestUri) ?>') {
        renderNavigationTiming();
        btn.disabled = false;
        btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="5 3 19 12 5 21 5 3"/></svg> Analyze Page Speed';
        return;
    }

    // Otherwise, perform a live fetch and extract ResourceTiming
    var fullUrl = new URL(url, window.location.origin).href;
    var cacheBustUrl = fullUrl + (fullUrl.includes('?') ? '&' : '?') + '_perf_bench=' + Date.now();

    var fetchStart = performance.now();
    fetch(cacheBustUrl, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function (response) {
            var fetchEnd = performance.now();
            var totalFetchMs = Math.round(fetchEnd - fetchStart);

            // Look up the exact resource entry in Performance API
            var entries = performance.getEntriesByName(cacheBustUrl);
            var entry = entries && entries.length ? entries[entries.length - 1] : null;

            if (entry) {
                renderTimingEntry(entry, totalFetchMs, url);
            } else {
                // Fallback using total duration
                renderBasicTiming(totalFetchMs, url);
            }
        })
        .catch(function (err) {
            alert('Failed to fetch URL: ' + err.message);
        })
        .finally(function () {
            btn.disabled = false;
            btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="5 3 19 12 5 21 5 3"/></svg> Analyze Page Speed';
        });
}

function getEvaluation(duration, thresholds) {
    if (duration <= thresholds.fast) {
        return { label: 'Optimal', badgeClass: 'badge-fast', color: '#10b981' };
    } else if (duration <= thresholds.warn) {
        return { label: 'Moderate', badgeClass: 'badge-warn', color: '#f59e0b' };
    } else {
        return { label: 'Bottleneck', badgeClass: 'badge-bad', color: '#ef4444' };
    }
}

function renderTimingEntry(entry, totalMs, title) {
    // Chrome / W3C Resource Timing properties
    var stalled = Math.max(0, (entry.requestStart > 0 && entry.startTime > 0) ? (entry.requestStart - entry.startTime) : 0);
    var dns = Math.max(0, (entry.domainLookupEnd > 0 && entry.domainLookupStart > 0) ? (entry.domainLookupEnd - entry.domainLookupStart) : 0);
    var tcp = Math.max(0, (entry.connectEnd > 0 && entry.connectStart > 0) ? (entry.connectEnd - entry.connectStart) : 0);
    var ssl = Math.max(0, (entry.secureConnectionStart > 0 && entry.connectEnd > 0) ? (entry.connectEnd - entry.secureConnectionStart) : 0);
    var ttfb = Math.max(0, (entry.responseStart > 0 && entry.requestStart > 0) ? (entry.responseStart - entry.requestStart) : 0);
    var download = Math.max(0, (entry.responseEnd > 0 && entry.responseStart > 0) ? (entry.responseEnd - entry.responseStart) : 0);

    // If connection was reused, dns/tcp/ssl will be 0
    var calculatedTotal = Math.round(entry.duration || totalMs);

    var stages = [
        {
            id: 'stalled',
            name: 'Connection Stalled / Queueing',
            desc: 'Browser waiting for available TCP connection socket or pending session lock',
            ms: Math.round(stalled),
            thresholds: { fast: 10, warn: 80 }
        },
        {
            id: 'dns',
            name: 'DNS Lookup',
            desc: 'Translating domain name to server IP address',
            ms: Math.round(dns),
            thresholds: { fast: 20, warn: 80 }
        },
        {
            id: 'tcp',
            name: 'TCP Handshake',
            desc: 'SYN/ACK round-trip connection establishment',
            ms: Math.round(tcp),
            thresholds: { fast: 30, warn: 100 }
        },
        {
            id: 'ssl',
            name: 'SSL / TLS Handshake',
            desc: 'Certificate verification & encryption negotiation',
            ms: Math.round(ssl),
            thresholds: { fast: 40, warn: 120 }
        },
        {
            id: 'ttfb',
            name: 'Waiting for Server (TTFB)',
            desc: 'Server processing: PHP boot, Laravel middleware, MySQL queries, Blade view rendering',
            ms: Math.round(ttfb || (totalMs - stalled - download)),
            thresholds: { fast: 150, warn: 350 }
        },
        {
            id: 'download',
            name: 'Content Download',
            desc: 'Receiving HTTP response payload over network',
            ms: Math.round(download),
            thresholds: { fast: 30, warn: 100 }
        }
    ];

    displayDiagnostics(stages, calculatedTotal, title);
}

function renderNavigationTiming() {
    var nav = performance.getEntriesByType('navigation')[0];
    if (!nav) {
        var timing = window.performance.timing;
        if (!timing) return;
        var start = timing.navigationStart;
        var stalled = Math.max(0, timing.requestStart - start);
        var dns = Math.max(0, timing.domainLookupEnd - timing.domainLookupStart);
        var tcp = Math.max(0, timing.connectEnd - timing.connectStart);
        var ssl = timing.secureConnectionStart ? Math.max(0, timing.connectEnd - timing.secureConnectionStart) : 0;
        var ttfb = Math.max(0, timing.responseStart - timing.requestStart);
        var download = Math.max(0, timing.responseEnd - timing.responseStart);
        var total = Math.max(1, timing.loadEventEnd - start || timing.responseEnd - start);
    } else {
        var stalled = Math.max(0, nav.requestStart - nav.startTime);
        var dns = Math.max(0, nav.domainLookupEnd - nav.domainLookupStart);
        var tcp = Math.max(0, nav.connectEnd - nav.connectStart);
        var ssl = nav.secureConnectionStart ? Math.max(0, nav.connectEnd - nav.secureConnectionStart) : 0;
        var ttfb = Math.max(0, nav.responseStart - nav.requestStart);
        var download = Math.max(0, nav.responseEnd - nav.responseStart);
        var total = Math.round(nav.duration || nav.responseEnd);
    }

    var stages = [
        { id: 'stalled', name: 'Connection Stalled / Queueing', desc: 'Browser socket pool queue or session lock', ms: Math.round(stalled), thresholds: { fast: 10, warn: 80 } },
        { id: 'dns', name: 'DNS Lookup', desc: 'Domain to IP resolution', ms: Math.round(dns), thresholds: { fast: 20, warn: 80 } },
        { id: 'tcp', name: 'TCP Handshake', desc: 'Server connection handshake', ms: Math.round(tcp), thresholds: { fast: 30, warn: 100 } },
        { id: 'ssl', name: 'SSL / TLS Handshake', desc: 'TLS encryption handshake', ms: Math.round(ssl), thresholds: { fast: 40, warn: 120 } },
        { id: 'ttfb', name: 'Waiting for Server (TTFB)', desc: 'Backend PHP, Laravel lifecycle, MySQL database queries', ms: Math.round(ttfb), thresholds: { fast: 150, warn: 350 } },
        { id: 'download', name: 'Content Download', desc: 'Transferring HTML bytes', ms: Math.round(download), thresholds: { fast: 30, warn: 100 } }
    ];

    displayDiagnostics(stages, total, 'Current Page (Diagnostics)');
}

function displayDiagnostics(stages, totalMs, targetName) {
    var tbody = document.getElementById('timing-tbody');
    tbody.innerHTML = '';

    // Find the slowest stage
    var maxStage = stages[0];
    stages.forEach(function (s) {
        if (s.ms > maxStage.ms) {
            maxStage = s;
        }
    });

    var maxPercent = totalMs > 0 ? Math.round((maxStage.ms / totalMs) * 100) : 0;

    // Update Hero
    var titleEl = document.getElementById('bottleneck-title');
    var subtitleEl = document.getElementById('bottleneck-subtitle');
    var badgeEl = document.getElementById('bottleneck-badge');

    titleEl.innerHTML = 'Bottleneck Detected: <span style="color:#f87171;">' + maxStage.name + '</span> (' + maxStage.ms + ' ms)';
    subtitleEl.innerHTML = targetName + ' — Total Duration: <strong style="color:#fff;">' + totalMs + ' ms</strong>. This single stage consumed <strong style="color:#f87171;">' + maxPercent + '%</strong> of total loading time.';

    var evalObj = getEvaluation(maxStage.ms, maxStage.thresholds);
    badgeEl.innerHTML = '<span class="badge-status ' + evalObj.badgeClass + '" style="font-size:13px;padding:6px 12px;">' + evalObj.label + '</span>';

    // Populate table
    stages.forEach(function (stage) {
        var pct = totalMs > 0 ? Math.min(100, Math.round((stage.ms / totalMs) * 100)) : 0;
        var evaluation = getEvaluation(stage.ms, stage.thresholds);

        var tr = document.createElement('tr');
        tr.innerHTML = '<td>' +
            '<div style="font-weight:600;color:#fff;">' + stage.name + '</div>' +
            '<div style="font-size:11px;color:var(--text-muted);">' + stage.desc + '</div>' +
            '</td>' +
            '<td class="mono" style="font-weight:600;font-size:13px;">' + stage.ms + ' ms</td>' +
            '<td class="mono" style="color:var(--text-secondary);">' + pct + '%</td>' +
            '<td>' +
            '<div class="bar-container">' +
            '<div class="bar-fill" style="width:' + pct + '%;background:' + evaluation.color + ';"></div>' +
            '</div>' +
            '</td>' +
            '<td><span class="badge-status ' + evaluation.badgeClass + '">' + evaluation.label + '</span></td>';

        tbody.appendChild(tr);
    });

    // Populate Actionable Recommendations Box
    renderRecommendations(maxStage, stages);
}

function renderRecommendations(bottleneck, stages) {
    var container = document.getElementById('recommendations-content');
    var html = '';

    if (bottleneck.id === 'stalled') {
        html += '<div class="fix-box">' +
            '<div class="fix-title" style="color:#f87171;">' +
            '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>' +
            'Why is "Connection Stalled / Queueing" taking ' + bottleneck.ms + ' ms?' +
            '</div>' +
            '<div class="fix-desc">' +
            'The browser paused the request before sending it out on the wire. This happens for three common reasons:' +
            '</div>' +
            '<ul class="fix-list">' +
            '<li><strong>HTTP/1.1 6-Connection Limit:</strong> Under HTTP/1.1, Chrome restricts each domain to 6 simultaneous TCP connections. If background assets or prefetch requests occupy them, subsequent requests stall waiting for a free socket. <em>(Fix: Enable HTTP/2 on your web server)</em>.</li>' +
            '<li><strong>PHP Session File Lock Contention:</strong> Laravel\'s default session driver is <code>file</code>. When two requests with the same session cookie hit the server, PHP locks the session file. The second request is forced to stall until the first finishes.</li>' +
            '<li><strong>Hover Prefetcher Collision:</strong> If a script in your layout prefetches pages on hover, clicking the link while the prefetch is in flight causes the click to stall waiting for the prefetch request.</li>' +
            '</ul>' +
            '</div>';
    } else if (bottleneck.id === 'ttfb') {
        html += '<div class="fix-box">' +
            '<div class="fix-title" style="color:#f87171;">' +
            '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>' +
            'Why is "Waiting for Server (TTFB)" taking ' + bottleneck.ms + ' ms?' +
            '</div>' +
            '<div class="fix-desc">' +
            'Time to First Byte is entirely backend processing on your server. Here are the primary culprits:' +
            '</div>' +
            '<ul class="fix-list">' +
            '<li><strong>PHP OPcache Not Enabled or Warmed:</strong> Check the "PHP OPcache" card above. If OPcache is disabled, PHP must re-read and parse hundreds of Laravel and vendor files on every click, adding 200–400ms alone.</li>' +
            '<li><strong>Laravel Uncached Configurations:</strong> Run <code>php artisan config:cache</code> and <code>php artisan route:cache</code> on production to avoid filesystem directory scanning.</li>' +
            '<li><strong>Database Query Count:</strong> If an admin page executes 40+ queries (N+1 query problem), MySQL latency adds up. Check query execution time in the "Database Latency" card above.</li>' +
            '<li><strong>Middleware Overhead:</strong> Middlewares querying <code>information_schema</code> or running <code>Schema::hasTable</code> on every request add significant TTFB delay.</li>' +
            '</ul>' +
            '</div>';
    } else if (bottleneck.id === 'dns') {
        html += '<div class="fix-box warn">' +
            '<div class="fix-title" style="color:#fbbf24;">DNS Lookup is taking ' + bottleneck.ms + ' ms</div>' +
            '<div class="fix-desc">The browser is spending extra time resolving your domain. If testing on localhost on Windows, ensure your host resolves to <code>127.0.0.1</code> rather than IPv6 <code>::1</code>. In production, use Cloudflare DNS or verify your nameservers.</div>' +
            '</div>';
    } else if (bottleneck.id === 'ssl' || bottleneck.id === 'tcp') {
        html += '<div class="fix-box warn">' +
            '<div class="fix-title" style="color:#fbbf24;">Connection Handshake taking ' + bottleneck.ms + ' ms</div>' +
            '<div class="fix-desc">TCP/TLS round-trips can be slow over high-latency networks. Ensure HTTP Keep-Alive and TLS 1.3 are enabled on your server to allow connection reuse.</div>' +
            '</div>';
    } else {
        html += '<div class="fix-box good">' +
            '<div class="fix-title" style="color:#34d399;">Connection Performance is in Good Health</div>' +
            '<div class="fix-desc">All connection stages are within normal operating thresholds. No critical bottlenecks detected.</div>' +
            '</div>';
    }

    container.innerHTML = html;
}

// Automatically analyze the diagnostics page upon loading
window.addEventListener('load', function () {
    setTimeout(renderNavigationTiming, 100);
});
</script>

</body>
</html>
