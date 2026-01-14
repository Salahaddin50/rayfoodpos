<?php

namespace App\Services;

use Exception;
use App\Libraries\AppLibrary;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PermissionRequest;
use App\Libraries\QueryExceptionLibrary;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionService
{

    /**
     * @throws Exception
     */
    public function permission(Role $role) : object
    {
        try {
            $permissions     = Permission::get();
            $rolePermissions = Permission::join(
                "role_has_permissions",
                "role_has_permissions.permission_id",
                "=",
                "permissions.id"
            )->where("role_has_permissions.role_id", $role->id)->get()->pluck('name', 'id');
            return AppLibrary::permissionWithAccess($permissions, $rolePermissions);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(PermissionRequest $request, Role $role) : Role
    {
        try {
            $updated = $role->syncPermissions(Permission::whereIn('id', $request->get('permissions'))->get());

            // Ensure Spatie permission cache is cleared so changes apply immediately.
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            return $updated;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }
}
