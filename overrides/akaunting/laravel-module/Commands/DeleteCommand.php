<?php

namespace Akaunting\Module\Commands;

use Illuminate\Console\Command;

/**
 * MobiTrack: Module system is not used. Stub command to satisfy framework wiring.
 */
class DeleteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:delete {alias} {company} {locale=en-GB}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete the specified module (disabled in MobiTrack).';

    public function handle()
    {
        $this->info('Module management is disabled in MobiTrack.');
    }
}
