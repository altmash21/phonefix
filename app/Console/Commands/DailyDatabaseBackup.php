<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MobileShop\DatabaseBackupService;

class DailyDatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mobileshop:backup-db {--tag=automated : Optional identifier tag for the backup file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a full SQL dump backup of the database and prune old backups according to retention policy';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tag = $this->option('tag') ?: 'automated';
        $this->info("Initiating database backup (Tag: {$tag})...");

        try {
            $backupResult = DatabaseBackupService::createBackup($tag);

            if ($backupResult['success']) {
                $this->info("✓ Backup created successfully!");
                $this->line("  File: {$backupResult['filename']}");
                $this->line("  Size: {$backupResult['size_formatted']}");
                $this->line("  Method: " . ($backupResult['method'] ?? 'unknown'));

                // Update settings with last backup timestamp
                $settings = DatabaseBackupService::getBackupSettings();
                $settings['last_backup_at'] = now()->toDateTimeString();
                $settings['last_backup_file'] = $backupResult['filename'];
                $settings['last_backup_status'] = 'success';
                DatabaseBackupService::saveBackupSettings($settings);

                // Prune according to retention policy
                $retentionDays = intval($settings['retention_days'] ?? 14);
                $deletedCount = DatabaseBackupService::pruneOldBackups($retentionDays);
                if ($deletedCount > 0) {
                    $this->comment("✓ Pruned {$deletedCount} backups older than {$retentionDays} days.");
                }

                return 0;
            } else {
                $this->error("✗ Backup failed: " . ($backupResult['error'] ?? 'Unknown error'));
                
                $settings = DatabaseBackupService::getBackupSettings();
                $settings['last_backup_status'] = 'failed: ' . ($backupResult['error'] ?? 'unknown');
                DatabaseBackupService::saveBackupSettings($settings);

                return 1;
            }
        } catch (\Throwable $e) {
            $this->error("✗ Exception during backup: " . $e->getMessage());
            return 1;
        }
    }
}
