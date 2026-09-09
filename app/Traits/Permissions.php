<?php

namespace App\Traits;

use App\Traits\SearchString;
use App\Traits\Translations;
use App\Traits\Permissions\HasRolePermissions;
use App\Traits\Permissions\HasModulePermissions;
use App\Traits\Permissions\HasPermissionManagement;

/**
 * Permissions Trait
 * Decomposed into focused concerns under App\Traits\Permissions\:
 * - HasRolePermissions (role attachment, detachment, default admin/portal roles)
 * - HasModulePermissions (report, widget, setting permission creation and attachment)
 * - HasPermissionManagement (role/permission CRUD, controller assignment, menu access)
 */
trait Permissions
{
    use SearchString, Translations;
    use HasRolePermissions;
    use HasModulePermissions;
    use HasPermissionManagement;
}
