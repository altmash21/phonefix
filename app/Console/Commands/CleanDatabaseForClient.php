<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MobileShop\DatabaseBackupService;

class CleanDatabaseForClient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mobileshop:clean-database {--force : Bypass confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Wipe all stock, suppliers, purchase orders, sales invoices, khata ledgers, and attachments to ship a pristine brand-new system for client delivery';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->warn('================================================================');
        $this->warn('  MobiTrack / PhoneFix: Client Delivery Complete Database Wipe ');
        $this->warn('================================================================');
        $this->line('This command will purge:');
        $this->line(' - ALL stock (New & Used phones, accessories, parts, gifts, defectives)');
        $this->line(' - ALL suppliers (Vendor profiles, purchase orders, GRNs, payments)');
        $this->line(' - ALL sales invoices & sales return credit notes');
        $this->line(' - ALL customers & khata (udhari) transactions');
        $this->line(' - ALL store expense vouchers & repair service tickets');
        $this->line(' - Reset all sequence counters back to start at #0001');
        $this->line(' - Clean uploaded temporary photos & attachments');
        $this->line('Super Admin credentials and catalog masters will be preserved.');
        $this->newLine();

        if (!$this->option('force')) {
            $confirm = $this->ask("Type 'RESET' in capital letters to proceed with complete wipe");
            if (trim((string) $confirm) !== 'RESET') {
                $this->error('Wipe aborted. Confirmation phrase did not match.');
                return 1;
            }
        }

        $this->info('Initiating pre-reset backup and database purge...');

        try {
            $result = DatabaseBackupService::resetDatabaseForClientHandover([
                'wipe_stock'      => true,
                'wipe_suppliers'  => true,
                'clean_media'     => true,
                'reset_sequences' => true,
                'preserve_staff'  => true,
            ]);

            if ($result['success']) {
                $this->info('✓ ' . $result['message']);
                $this->line("  Pre-reset Snapshot: " . ($result['snapshot'] ?? 'N/A'));
                $this->line("  Total Records Purged: " . ($result['total_records'] ?? 0));
                $this->line("  Cleaned Media Files: " . ($result['cleaned_media_files'] ?? 0));
                $this->newLine();
                $this->info('System is 100% brand new and ready to ship to client!');
                return 0;
            } else {
                $this->error('✗ Reset failed: ' . ($result['error'] ?? 'Unknown error'));
                return 1;
            }
        } catch (\Throwable $e) {
            $this->error('✗ Exception during database wipe: ' . $e->getMessage());
            return 1;
        }
    }
}
