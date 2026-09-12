<?php

namespace App\Services\MobileShop;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DatabaseBackupService
{
    /**
     * Get the directory where database backups are stored.
     */
    public static function getBackupDirectory(): string
    {
        $dir = storage_path('app/backups');
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true, true);
        }
        return $dir;
    }

    /**
     * Generate a complete database backup (.sql file)
     * Uses mysqldump when available, with a full pure-PHP PDO fallback.
     */
    public static function createBackup(?string $tag = null): array
    {
        $dir = self::getBackupDirectory();
        $dateStr = date('Y-m-d-His');
        $cleanTag = $tag ? '-' . preg_replace('/[^a-zA-Z0-9_\-]/', '', $tag) : '';
        $filename = "backup-{$dateStr}{$cleanTag}.sql";
        $filepath = "{$dir}/{$filename}";

        $dbName = config('database.connections.mysql.database') ?? env('DB_DATABASE', 'forge');
        $dbUser = config('database.connections.mysql.username') ?? env('DB_USERNAME', 'forge');
        $dbPass = config('database.connections.mysql.password') ?? env('DB_PASSWORD', '');
        $dbHost = config('database.connections.mysql.host') ?? env('DB_HOST', '127.0.0.1');
        $dbPort = config('database.connections.mysql.port') ?? env('DB_PORT', '3306');

        $methodUsed = 'php';
        $dumpSuccess = false;

        // Try native mysqldump CLI first
        try {
            $passParam = !empty($dbPass) ? "--password=" . escapeshellarg($dbPass) : '';
            $cmd = sprintf(
                'mysqldump --host=%s --port=%s --user=%s %s --single-transaction --quick --skip-lock-tables %s > %s 2>&1',
                escapeshellarg($dbHost),
                escapeshellarg($dbPort),
                escapeshellarg($dbUser),
                $passParam,
                escapeshellarg($dbName),
                escapeshellarg($filepath)
            );

            @exec($cmd, $output, $returnCode);

            if ($returnCode === 0 && File::exists($filepath) && File::size($filepath) > 1024) {
                $dumpSuccess = true;
                $methodUsed = 'mysqldump';
            }
        } catch (\Throwable $e) {
            Log::info("mysqldump CLI skipped, using PHP fallback: " . $e->getMessage());
        }

        // Pure-PHP dump fallback if CLI failed
        if (!$dumpSuccess) {
            try {
                self::dumpViaPhp($filepath, $dbName);
                $dumpSuccess = true;
                $methodUsed = 'php_native';
            } catch (\Throwable $e) {
                Log::error("Database backup failed in PHP fallback: " . $e->getMessage());
                return [
                    'success' => false,
                    'message' => 'Backup failed: ' . $e->getMessage(),
                ];
            }
        }

        $sizeBytes = File::exists($filepath) ? File::size($filepath) : 0;

        return [
            'success'        => true,
            'filename'       => $filename,
            'filepath'       => $filepath,
            'size'           => $sizeBytes,
            'size_formatted' => self::formatBytes($sizeBytes),
            'method'         => $methodUsed,
            'created_at'     => now()->toDateTimeString(),
            'message'        => "Database backup created successfully ({$filename})",
        ];
    }

    /**
     * Pure-PHP database dumper fallback
     */
    protected static function dumpViaPhp(string $filepath, string $dbName): void
    {
        $handle = fopen($filepath, 'w');
        if (!$handle) {
            throw new \RuntimeException("Unable to open file for writing: {$filepath}");
        }

        // Header SQL
        $header = "-- ========================================================\n"
                . "-- MobiTrack ERP Database Dump\n"
                . "-- Host Database: {$dbName}\n"
                . "-- Generated: " . date('Y-m-d H:i:s') . "\n"
                . "-- ========================================================\n\n"
                . "SET FOREIGN_KEY_CHECKS=0;\n"
                . "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n"
                . "SET time_zone = \"+00:00\";\n\n";
        fwrite($handle, $header);

        $tables = DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"');
        $pdo = DB::connection()->getPdo();

        foreach ($tables as $tObj) {
            $vals = array_values((array) $tObj);
            $table = $vals[0];

            fwrite($handle, "\n-- --------------------------------------------------------\n");
            fwrite($handle, "-- Table structure for `{$table}`\n");
            fwrite($handle, "-- --------------------------------------------------------\n");
            fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");

            $createTableObj = DB::select("SHOW CREATE TABLE `{$table}`");
            if (!empty($createTableObj)) {
                $createVals = array_values((array) $createTableObj[0]);
                $createSql = $createVals[1] ?? '';
                fwrite($handle, $createSql . ";\n\n");
            }

            // Dump data in chunks
            $count = DB::table($table)->count();
            if ($count > 0) {
                fwrite($handle, "-- Dumping data for `{$table}` ({$count} rows)\n");
                
                DB::table($table)->orderBy(DB::raw('1'))->chunk(250, function ($rows) use ($handle, $table, $pdo) {
                    if ($rows->isEmpty()) return;

                    $first = $rows->first();
                    $cols = array_keys((array) $first);
                    $colsEscaped = array_map(fn($c) => "`{$c}`", $cols);

                    $insertSql = "INSERT INTO `{$table}` (" . implode(', ', $colsEscaped) . ") VALUES\n";
                    fwrite($handle, $insertSql);

                    $valueLines = [];
                    foreach ($rows as $row) {
                        $values = [];
                        foreach ((array) $row as $val) {
                            if (is_null($val)) {
                                $values[] = 'NULL';
                            } elseif (is_numeric($val)) {
                                $values[] = $val;
                            } else {
                                $values[] = $pdo->quote((string) $val);
                            }
                        }
                        $valueLines[] = "(" . implode(', ', $values) . ")";
                    }
                    fwrite($handle, implode(",\n", $valueLines) . ";\n\n");
                });
            }
        }

        fwrite($handle, "\nSET FOREIGN_KEY_CHECKS=1;\n");
        fwrite($handle, "-- Dump completed on " . date('Y-m-d H:i:s') . "\n");
        fclose($handle);
    }

    /**
     * List all database backup files stored in storage
     */
    public static function listBackups(): array
    {
        $dir = self::getBackupDirectory();
        $files = File::glob("{$dir}/*.sql");
        $backups = [];

        foreach ($files as $file) {
            $filename = basename($file);
            $size = File::size($file);
            $mtime = File::lastModified($file);
            $date = Carbon::createFromTimestamp($mtime);

            $backups[] = [
                'filename'       => $filename,
                'path'           => $file,
                'size'           => $size,
                'size_formatted' => self::formatBytes($size),
                'created_at'     => $date->format('d M Y, h:i A'),
                'timestamp'      => $mtime,
                'age'            => $date->diffForHumans(),
            ];
        }

        // Sort by newest first
        usort($backups, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        return $backups;
    }

    /**
     * Delete a backup file
     */
    public static function deleteBackup(string $filename): bool
    {
        // Path traversal guard
        $cleanName = basename($filename);
        $file = self::getBackupDirectory() . '/' . $cleanName;

        if (File::exists($file) && str_ends_with($cleanName, '.sql')) {
            return File::delete($file);
        }

        return false;
    }

    /**
     * Prune backups older than $daysToKeep
     */
    public static function pruneOldBackups(int $daysToKeep = 14): int
    {
        $backups = self::listBackups();
        $threshold = now()->subDays($daysToKeep)->timestamp;
        $deleted = 0;

        foreach ($backups as $b) {
            if ($b['timestamp'] < $threshold) {
                if (self::deleteBackup($b['filename'])) {
                    $deleted++;
                }
            }
        }

        return $deleted;
    }

    /**
     * Gather deep system telemetry & database metrics
     */
    public static function getTelemetry(int $companyId = 1): array
    {
        $dbName = config('database.connections.mysql.database') ?? env('DB_DATABASE', 'forge');
        $prefix = DB::getTablePrefix();

        // Total DB size from information_schema
        $dbSizeMb = 0;
        $tableCount = 0;
        try {
            $sizeRow = DB::selectOne("
                SELECT 
                    ROUND(SUM(data_length + index_length) / (1024 * 1024), 2) AS size_mb,
                    COUNT(*) AS table_count
                FROM information_schema.TABLES
                WHERE table_schema = ?
            ", [$dbName]);

            if ($sizeRow) {
                $dbSizeMb = (float) ($sizeRow->size_mb ?? 0);
                $tableCount = (int) ($sizeRow->table_count ?? 0);
            }
        } catch (\Throwable $e) {
            Log::warning('Could not get DB size: ' . $e->getMessage());
        }

        // Safe table row counts
        $deviceCountNew      = self::safeCount('ms_mobile_devices', ['device_type' => 'new']);
        $deviceCountUsed     = self::safeCount('ms_mobile_devices', ['device_type' => 'secondhand']);
        $deviceCountTotal    = self::safeCount('ms_mobile_devices');
        $partsItemCount      = self::safeCount('ms_parts_inventory');
        $categoryCount       = self::safeCount('ms_part_categories');
        $phoneSalesCount     = self::safeCount('ms_mobile_sales');
        $accessorySalesCount = self::safeCount('ms_accessory_sales');
        $purchaseCount       = self::safeCount('ms_purchase_orders');
        $repairCount         = self::safeCount('ms_repairs');
        $customerCount       = self::safeCount('ms_customers');
        $khataTransCount     = self::safeCount('ms_customer_khata_transactions');
        $emiProviderCount    = self::safeCount('ms_emi_providers');
        $supplierCount       = self::safeCount('ms_suppliers');
        $sessionCount        = self::safeCount('ms_login_sessions');
        $inviteTokenCount    = self::safeCount('ms_employee_invites', ['status' => 'active']);
        $usersCount          = DB::table('users')->count();

        // Safe revenue calculations
        $phoneRevenue     = self::safeSum('ms_mobile_sales', 'total_amount');
        $accessoryRevenue = self::safeSum('ms_accessory_sales', 'total_amount');
        $grossRevenue     = $phoneRevenue + $accessoryRevenue;
        $purchaseSpend    = self::safeSum('ms_purchase_orders', 'total_amount');
        $customerKhataDue = self::safeSum('ms_customers', 'khata_balance');

        // Detailed row counts for all MobileShop tables
        $tablesList = DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"');
        $firstProp = 'Tables_in_' . $dbName;
        $mobileshopTableCount = 0;
        $tableRowsMap = [];

        foreach ($tablesList as $t) {
            $tArr = (array) $t;
            $tableName = $tArr[$firstProp] ?? reset($tArr);
            if (str_contains($tableName, 'ms_')) {
                $mobileshopTableCount++;
                try {
                    $tableRowsMap[$tableName] = DB::table(str_replace($prefix, '', $tableName))->count();
                } catch (\Throwable $e) {
                    $tableRowsMap[$tableName] = 0;
                }
            }
        }

        // Server specs
        $mysqlVersion = 'Unknown';
        try {
            $verRow = DB::selectOne('SELECT VERSION() AS ver');
            $mysqlVersion = $verRow->ver ?? 'Unknown';
        } catch (\Throwable $e) {}

        $freeDisk = @disk_free_space(base_path());
        $totalDisk = @disk_total_space(base_path());
        $freeDiskGb = $freeDisk ? round($freeDisk / (1024 * 1024 * 1024), 2) : 0;

        $backups = self::listBackups();
        $backupSettings = self::getBackupSettings();

        return [
            'generated_at' => now()->format('d M Y, h:i A'),
            'database' => [
                'name'               => $dbName,
                'prefix'             => $prefix,
                'size_mb'            => $dbSizeMb,
                'database_size_mb'   => $dbSizeMb,
                'table_count'        => $tableCount,
                'total_tables'       => $tableCount,
                'mobileshop_tables'  => $mobileshopTableCount,
                'mysql_ver'          => $mysqlVersion,
            ],
            'commercial' => [
                'total_sales_turnover'    => $grossRevenue,
                'gross_revenue'           => $grossRevenue,
                'invoices_count'          => $phoneSalesCount + $accessorySalesCount,
                'phone_sales_count'       => $phoneSalesCount,
                'acc_sales_count'         => $accessorySalesCount,
                'phone_revenue'           => $phoneRevenue,
                'accessory_revenue'       => $accessoryRevenue,
                'purchase_spend'          => $purchaseSpend,
                'total_khata_receivables' => $customerKhataDue,
            ],
            'inventory' => [
                'new_phones_stock'    => $deviceCountNew,
                'secondhand_stock'    => $deviceCountUsed,
                'accessories_stock'   => $partsItemCount,
                'repair_orders'       => $repairCount,
                'devices_total'       => $deviceCountTotal,
                'category_count'      => $categoryCount,
                'suppliers_count'     => $supplierCount,
                'customers_count'     => $customerCount,
            ],
            'server' => [
                'php_version'        => PHP_VERSION,
                'mysql_version'      => $mysqlVersion,
                'db_host'            => config('database.connections.mysql.host') ?? '127.0.0.1',
                'laravel_ver'        => app()->version(),
                'memory_limit'       => ini_get('memory_limit') ?: 'N/A',
                'max_execution_time' => ini_get('max_execution_time') ?: 'N/A',
                'disk_free_gb'       => $freeDiskGb,
                'memory_usage'       => self::formatBytes(memory_get_usage(true)),
                'free_disk'          => $freeDisk ? self::formatBytes($freeDisk) : 'N/A',
                'total_disk'         => $totalDisk ? self::formatBytes($totalDisk) : 'N/A',
                'server_os'          => PHP_OS . ' (' . php_uname('s') . ')',
            ],
            'tables' => $tableRowsMap,
            'backups' => [
                'list'           => $backups,
                'count'          => count($backups),
                'latest'         => $backups[0] ?? null,
                'settings'       => $backupSettings,
            ],
        ];
    }

    /**
     * Safely calculate SUM of column if table and column exist
     */
    protected static function safeSum(string $table, string $column, array $where = []): float
    {
        try {
            if (!Schema::hasTable($table)) {
                return 0.0;
            }
            if (!Schema::hasColumn($table, $column)) {
                return 0.0;
            }
            $query = DB::table($table);
            if (!empty($where)) {
                $query->where($where);
            }
            return (float) ($query->sum($column) ?? 0.0);
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    /**
     * Get or set daily automated backup settings
     */
    public static function getBackupSettings(): array
    {
        $settingsFile = storage_path('app/backup_settings.json');
        $defaults = [
            'daily_enabled'   => true,
            'backup_hour'     => '00:00',
            'retention_days'  => 14,
            'last_run_at'     => null,
            'last_run_status' => null,
        ];

        if (File::exists($settingsFile)) {
            $saved = json_decode(File::get($settingsFile), true);
            if (is_array($saved)) {
                return array_merge($defaults, $saved);
            }
        }

        return $defaults;
    }

    public static function saveBackupSettings(array $settings): bool
    {
        $current = self::getBackupSettings();
        $merged = array_merge($current, $settings);
        return (bool) File::put(storage_path('app/backup_settings.json'), json_encode($merged, JSON_PRETTY_PRINT));
    }

    /**
     * Fresh Client Provisioning: Reset database to zero-state before client handover.
     */
    public static function resetDatabaseForClientHandover(array $options = []): array
    {
        // Step 1: Automatic emergency backup snapshot before doing anything destructive
        $snapshot = self::createBackup('PRE-RESET-SNAPSHOT');

        // Step 2: Wipe transactional tables with foreign key checks disabled
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $tablesToWipe = [
            'ms_accessory_sale_items',
            'ms_accessory_sales',
            'ms_mobile_sales',
            'ms_purchase_order_items',
            'ms_purchase_orders',
            'ms_goods_receipt_items',
            'ms_goods_receipts',
            'ms_customer_khata_transactions',
            'ms_customers',
            'ms_sale_gifts',
            'ms_sales_returns',
            'ms_stock_audit_log',
            'ms_parts_inventory_history',
            'ms_repair_ticket_parts',
            'ms_repair_tickets',
            'ms_otp_tokens',
            'ms_password_reset_otps',
            'ms_login_sessions',
            'ms_employee_invites',
            'ms_supplier_credit_transactions',
            'ms_supplier_payments',
            'ms_sync_offsets',
            'ms_sync_queue',
        ];

        // Optional inventory devices wipe
        if (!empty($options['wipe_inventory'])) {
            $tablesToWipe[] = 'ms_mobile_devices';
            $tablesToWipe[] = 'ms_parts_inventory';
        }

        $wipedCounts = [];
        foreach ($tablesToWipe as $tbl) {
            try {
                if (DB::getSchemaBuilder()->hasTable($tbl)) {
                    $rows = DB::table($tbl)->count();
                    DB::table($tbl)->truncate();
                    $wipedCounts[$tbl] = $rows;
                }
            } catch (\Throwable $e) {
                Log::warning("Could not truncate {$tbl}: " . $e->getMessage());
            }
        }

        // Step 3: Reset invoice sequence counters back to start fresh at #0001
        try {
            if (DB::getSchemaBuilder()->hasTable('ms_invoice_sequences')) {
                DB::table('ms_invoice_sequences')->update([
                    'current_number' => 0,
                    'updated_at'     => now(),
                ]);
            }
        } catch (\Throwable $e) {}

        // Step 4: Reset advance wallets on Suppliers and advance pools on EMI providers to 0.00
        try {
            if (DB::getSchemaBuilder()->hasTable('ms_emi_providers')) {
                DB::table('ms_emi_providers')->update(['advance_balance' => 0.00, 'updated_at' => now()]);
            }
            if (DB::getSchemaBuilder()->hasTable('ms_suppliers')) {
                DB::table('ms_suppliers')->update(['credit_balance' => 0.00, 'updated_at' => now()]);
            }
            if (DB::getSchemaBuilder()->hasTable('ms_supplier_credit_wallets')) {
                DB::table('ms_supplier_credit_wallets')->truncate();
            }
        } catch (\Throwable $e) {}

        // Step 5: (Optional) Remove demo staff accounts, preserving primary Super Admin
        if (!empty($options['wipe_staff'])) {
            try {
                $superAdmin = DB::table('users')->where('email', 'admin@mobitrack.local')->first()
                           ?? DB::table('users')->orderBy('id')->first();

                if ($superAdmin) {
                    $otherUsers = DB::table('users')->where('id', '!=', $superAdmin->id)->pluck('id');
                    DB::table('user_roles')->whereIn('user_id', $otherUsers)->delete();
                    DB::table('user_companies')->whereIn('user_id', $otherUsers)->delete();
                    DB::table('users')->whereIn('id', $otherUsers)->delete();
                }
            } catch (\Throwable $e) {}
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Step 6: Clear caches
        try {
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            \Illuminate\Support\Facades\Artisan::call('view:clear');
        } catch (\Throwable $e) {}

        return [
            'success'       => true,
            'snapshot'      => $snapshot['filename'] ?? 'unknown',
            'wiped_tables'  => $wipedCounts,
            'total_records' => array_sum($wipedCounts),
            'message'       => 'Database successfully reset to pristine production state! Ready for client handover.',
        ];
    }

    /**
     * Safe table row count helper
     */
    protected static function safeCount(string $table, array $where = []): int
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable($table)) return 0;
            $q = DB::table($table);
            if (!empty($where)) {
                $q->where($where);
            }
            return (int) $q->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Human-readable byte formatter
     */
    public static function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
