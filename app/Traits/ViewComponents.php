<?php

namespace App\Traits;

use App\Traits\Modules;
use App\Traits\ViewComponents\HasViewConfig;
use App\Traits\ViewComponents\HasViewRoutes;
use App\Traits\ViewComponents\HasViewBulkAndUi;

/**
 * ViewComponents Trait
 * Decomposed into focused concerns under App\Traits\ViewComponents\:
 * - HasViewConfig (config resolution, text, permissions, settings)
 * - HasViewRoutes (CRUD route generation and parameters)
 * - HasViewBulkAndUi (bulk actions, empty pages, suggestions)
 */
trait ViewComponents
{
    use Modules;
    use HasViewConfig;
    use HasViewRoutes;
    use HasViewBulkAndUi;
}
