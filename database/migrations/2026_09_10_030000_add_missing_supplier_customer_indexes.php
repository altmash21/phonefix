<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function addIndexSafely(string $table, array $columns, string $indexName): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        try {
            $pfx = DB::getTablePrefix();
            $colList = '`' . implode('`, `', $columns) . '`';
            DB::statement("CREATE INDEX `{$indexName}` ON `{$pfx}{$table}` ({$colList})");
        } catch (\Throwable $e) {
            // Index already exists or driver unsupported — continue safely
        }
    }

    public function up(): void
    {
        // 1. Suppliers lookup by company and name
        $this->addIndexSafely('ms_suppliers', ['company_id', 'name'], 'idx_ms_suppliers_comp_name');

        // 2. Customers lookup by company and phone
        $this->addIndexSafely('ms_customers', ['company_id', 'phone'], 'idx_ms_customers_comp_phone');
    }

    public function down(): void
    {
        $indexes = [
            'ms_suppliers' => ['idx_ms_suppliers_comp_name'],
            'ms_customers' => ['idx_ms_customers_comp_phone'],
        ];

        $pfx = DB::getTablePrefix();
        foreach ($indexes as $table => $tableIndexes) {
            if (Schema::hasTable($table)) {
                foreach ($tableIndexes as $idx) {
                    try {
                        DB::statement("DROP INDEX `{$idx}` ON `{$pfx}{$table}`");
                    } catch (\Throwable $e) {}
                }
            }
        }
    }
};
