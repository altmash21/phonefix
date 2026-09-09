<?php

namespace App\Traits;

use App\Traits\Jobs;
use App\Traits\Sources;
use App\Traits\Translations;
use App\Traits\Import\HasImportEntityResolvers;
use App\Traits\Import\HasImportLookups;
use App\Traits\Import\HasImportViewConfig;

/**
 * Import Trait
 * Decomposed into focused concerns under App\Traits\Import\:
 * - HasImportEntityResolvers (resolves IDs from CSV/Excel row data)
 * - HasImportLookups (fallback auto-creation and lookups by name/email/rate)
 * - HasImportViewConfig (view setup, params, sample file paths)
 */
trait Import
{
    use Jobs, Sources, Translations;
    use HasImportEntityResolvers;
    use HasImportLookups;
    use HasImportViewConfig;
}
