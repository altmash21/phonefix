<?php

namespace App\Traits;

use App\Traits\SiteApi;
use App\Traits\Modules\HasModuleState;
use App\Traits\Modules\HasModuleStoreApi;
use App\Traits\Modules\HasModuleSubscriptionsAndTips;

/**
 * Modules Trait
 * Decomposed into focused concerns under App\Traits\Modules\:
 * - HasModuleState (local module existence, enabled/disabled checks, registration)
 * - HasModuleStoreApi (remote app store queries, releases, reviews, categories)
 * - HasModuleSubscriptionsAndTips (subscription limit checks, tips, notifications)
 */
trait Modules
{
    use SiteApi;
    use HasModuleState;
    use HasModuleStoreApi;
    use HasModuleSubscriptionsAndTips;
}
