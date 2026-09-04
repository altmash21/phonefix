<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- CODEBASE AUDIT START ---\n\n";

// 1. Audit MobileShopController for queries missing company_id
$controllerPath = __DIR__ . '/../app/Http/Controllers/MobileShopController.php';
$content = file_get_contents($controllerPath);

$tokens = token_get_all($content);
$count = 0;

// Scan lines in MobileShopController for potential issues
$lines = explode("\n", $content);
echo "1. Checking MobileShopController (total lines: " . count($lines) . ")\n";

// Look for DB::table calls
$dbTableIssues = [];
foreach ($lines as $num => $line) {
    if (preg_match('/DB::table\([\'"](ms_[a-z0-9_]+)[\'"]\)/', $line, $m)) {
        $tbl = $m[1];
        // Examine next 10 lines for company_id or where clause
        $snippet = implode(" ", array_slice($lines, $num, 8));
        // Tables that don't have company_id or are joining:
        $excludedTables = ['ms_invoice_sequences', 'ms_sync_queue', 'ms_sync_offsets'];
        if (!in_array($tbl, $excludedTables) && !str_contains($snippet, 'company_id') && !str_contains($snippet, 'insert') && !str_contains($snippet, 'join')) {
            $dbTableIssues[] = "Line " . ($num + 1) . ": Table {$tbl} -> " . trim($line);
        }
    }
}

echo "Found " . count($dbTableIssues) . " DB::table queries that might lack company_id in immediate lines:\n";
foreach (array_slice($dbTableIssues, 0, 20) as $issue) {
    echo "  [?] $issue\n";
}

// 2. Check for route declarations vs controller methods
echo "\n2. Checking all routes in routes/admin.php for MobileShopController:\n";
$routesContent = file_get_contents(__DIR__ . '/../routes/admin.php');
preg_match_all('/MobileShopController@([a-zA-Z0-9_]+)/', $routesContent, $mRoutes);
$refMethods = array_unique($mRoutes[1]);
$missingMethods = [];
$existingMethods = [];
$refClass = new ReflectionClass(\App\Http\Controllers\MobileShopController::class);
foreach ($refClass->getMethods(ReflectionMethod::IS_PUBLIC) as $m) {
    if ($m->class === \App\Http\Controllers\MobileShopController::class) {
        $existingMethods[] = $m->name;
    }
}
foreach ($refMethods as $method) {
    if (!in_array($method, $existingMethods)) {
        $missingMethods[] = $method;
    }
}
$rM = new ReflectionMethod(\App\Http\Controllers\MobileShopController::class, 'purchaseInvoice');
echo "purchaseInvoice is on line: " . $rM->getStartLine() . "\n";

$pfx = \Illuminate\Support\Facades\DB::getTablePrefix();
$col = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM `{$pfx}ms_accessory_sales` LIKE 'status'");
echo "ms_accessory_sales.status Column Type: " . ($col[0]->Type ?? 'unknown') . "\n";

$colSales = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM `{$pfx}ms_mobile_sales` LIKE 'status'");
echo "ms_mobile_sales.status Column Type: " . ($colSales[0]->Type ?? 'unknown') . "\n";

if (empty($missingMethods)) {
    echo "  [PASS] All " . count($refMethods) . " routes pointing to MobileShopController have existing controller methods.\n";
} else {
    echo "  [FAIL] Missing methods in MobileShopController:\n";
    foreach ($missingMethods as $mm) {
        echo "    - $mm\n";
    }
}

// 3. Check for view existence across MobileShopController
echo "\n3. Checking view references in MobileShopController:\n";
preg_match_all('/view\([\'"]([^\'"]+)[\'"]/', $content, $mViews);
$refViews = array_unique($mViews[1]);
$missingViews = [];
foreach ($refViews as $v) {
    if (!view()->exists($v)) {
        $missingViews[] = $v;
    }
}
if (empty($missingViews)) {
    echo "  [PASS] All " . count($refViews) . " views referenced in MobileShopController exist.\n";
} else {
    echo "  [FAIL] Missing views in MobileShopController:\n";
    foreach ($missingViews as $mv) {
        echo "    - $mv\n";
    }
}

// 4. Check blade files for broken route() calls
echo "\n4. Checking route() calls in resources/views/mobileshop/:\n";
$viewDir = __DIR__ . '/../resources/views/mobileshop';
$bladeFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewDir));
$missingRoutes = [];
$routeCount = 0;
foreach ($bladeFiles as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $bContent = file_get_contents($file->getPathname());
        preg_match_all('/route\([\'"]([a-zA-Z0-9_\.\-]+)[\'"]/', $bContent, $mR);
        foreach ($mR[1] as $rName) {
            $routeCount++;
            if (!\Illuminate\Support\Facades\Route::has($rName)) {
                $missingRoutes[] = $file->getFilename() . ": route('{$rName}') not found";
            }
        }
    }
}
$missingRoutes = array_unique($missingRoutes);
if (empty($missingRoutes)) {
    echo "  [PASS] All {$routeCount} route() calls in Blade views exist.\n";
} else {
    echo "  [FAIL] Missing routes in Blade templates:\n";
    foreach ($missingRoutes as $mr) {
        echo "    - $mr\n";
    }
}

// 6. Test Blade compilation of all views in mobileshop
echo "\n6. Checking Blade syntax by compiling all mobileshop views:\n";
$bladeCompiler = app('blade.compiler');
$viewFiles = glob(__DIR__ . '/../resources/views/mobileshop/*.blade.php');
$pdfViewFiles = glob(__DIR__ . '/../resources/views/mobileshop/pdf/*.blade.php');
$allViews = array_merge($viewFiles, $pdfViewFiles);
$compileErrors = [];

foreach ($allViews as $vf) {
    try {
        $content = file_get_contents($vf);
        $compiled = $bladeCompiler->compileString($content);
        // Syntax check the compiled PHP
        $tempFile = tempnam(sys_get_temp_dir(), 'blade_check_') . '.php';
        file_put_contents($tempFile, $compiled);
        $out = [];
        $ret = 0;
        exec("php -l " . escapeshellarg($tempFile) . " 2>&1", $out, $ret);
        unlink($tempFile);
        if ($ret !== 0) {
            $compileErrors[] = basename($vf) . " PHP syntax error: " . implode(" ", $out);
        }
    } catch (\Throwable $e) {
        $compileErrors[] = basename($vf) . " Compile error: " . $e->getMessage();
    }
}

if (empty($compileErrors)) {
    echo "  [PASS] All " . count($allViews) . " Blade views compiled and passed PHP linter without syntax errors.\n";
} else {
    echo "  [FAIL] Errors compiling Blade views:\n";
    foreach ($compileErrors as $ce) {
        echo "    - $ce\n";
    }
}



echo "\n--- CODEBASE AUDIT FINISHED ---\n";
