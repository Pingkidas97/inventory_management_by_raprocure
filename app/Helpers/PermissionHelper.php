<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

if (!function_exists('checkPermission')) {
    function checkPermission(string $moduleName, string $permissionType, string $moduleFor): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // If user has no parent_id (super admin in hierarchy), allow all permissions
        if (empty($user->parent_id)) {
            return true;
        }

        // Get user's role mapping
        $roleMapping = DB::table('user_role_mappings')
            ->where('user_id', $user->id)
            ->where('is_active', 1)
            ->first();

        if (!$roleMapping) {
            return false;
        }

        // Get module based on name and module_for (1 => Buyer, 2 => Vendor, 3 => Super Admin)
        $module = DB::table('modules')
            ->where('module_slug', $moduleName)
            ->where('module_for', $moduleFor)
            ->where('is_active', 1)
            ->first();

        if (!$module) {
            return false;
        }

        // Get permissions
        $permission = DB::table('user_role_module_permissions')
            ->where('user_role_id', $roleMapping->user_role_id)
            ->where('module_id', $module->id)
            ->where('is_active', 1)
            ->first();

        if (!$permission) {
            return false;
        }

        switch ($permissionType) {
            case 'view':
                return $permission->can_view == 1;
            case 'add':
                return $permission->can_add == 1;
            case 'edit':
                return $permission->can_edit == 1;
            case 'delete':
                return $permission->can_delete == 1;
            default:
                return false;
        }
    }
}
