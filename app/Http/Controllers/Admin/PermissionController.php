<?php

namespace App\Http\Controllers\Admin;


use Exception;
use Illuminate\Http\Request;
use App\Libraries\AppLibrary;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\RoleRequest;
use Spatie\Permission\Models\Role;
use App\Services\PermissionService;
use App\Http\Resources\RoleResource;
use App\Http\Requests\PermissionRequest;
use Spatie\Permission\Models\Permission;
use App\Http\Resources\PermissionResource;
use Illuminate\Routing\Controllers\Middleware;


class PermissionController extends AdminController
{
    private PermissionService $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        parent::__construct();
        $this->permissionService = $permissionService;
    }

    public static function middleware(): array
    {
        return [
            new Middleware('permission:settings', only: ['update']),
        ];
    }

    public function index(Role $role)
    {
        try {
            $permissions     = Permission::get();
            $rolePermissions = Permission::join(
                "role_has_permissions",
                "role_has_permissions.permission_id",
                "=",
                "permissions.id"
            )->where("role_has_permissions.role_id", $role->id)->get()->pluck('name', 'id');
            $permissions     = AppLibrary::permissionWithAccess($permissions, $rolePermissions);
            // IMPORTANT:
            // AppLibrary::numericToAssociativeArrayBuilder assumes parents appear before their children
            // in the provided array. Permission::get() returns rows by id, so if children are created later
            // (higher ids), they won't be attached and the Role & Permissions UI will lose the 4 CRUD checkboxes.
            // Sort into (parent, children) groups before building the tree.
            $permissionsArray = $permissions->toArray();
            usort($permissionsArray, function ($a, $b) {
                $aParent = (int)($a['parent'] ?? 0);
                $bParent = (int)($b['parent'] ?? 0);

                // Group key: parent id for children; own id for parents
                $aGroup = $aParent > 0 ? $aParent : (int)($a['id'] ?? 0);
                $bGroup = $bParent > 0 ? $bParent : (int)($b['id'] ?? 0);
                if ($aGroup !== $bGroup) {
                    return $aGroup <=> $bGroup;
                }

                // Within group: parent first, then children
                $aIsChild = $aParent > 0 ? 1 : 0;
                $bIsChild = $bParent > 0 ? 1 : 0;
                if ($aIsChild !== $bIsChild) {
                    return $aIsChild <=> $bIsChild;
                }

                return (int)($a['id'] ?? 0) <=> (int)($b['id'] ?? 0);
            });

            $permissions = AppLibrary::numericToAssociativeArrayBuilder($permissionsArray);

            // Keep a stable, user-friendly order in the Role & Permissions UI.
            // This also ensures newly added modules (e.g. Takeaway Types) appear near related sections.
            $priority = [
                'dashboard'     => 10,
                'items'         => 20,
                'dining-tables' => 30,
                'takeaway-types'=> 31,
            ];
            $indexed = [];
            foreach ($permissions as $idx => $p) {
                $p['_idx'] = $idx;
                $indexed[] = $p;
            }
            usort($indexed, function ($a, $b) use ($priority) {
                $ar = $priority[$a['name'] ?? ''] ?? 1000;
                $br = $priority[$b['name'] ?? ''] ?? 1000;
                if ($ar !== $br) return $ar <=> $br;
                return ($a['_idx'] ?? 0) <=> ($b['_idx'] ?? 0);
            });
            foreach ($indexed as &$p) {
                unset($p['_idx']);
            }
            $permissions = $indexed;

            return new JsonResponse(['data' => $permissions], 201);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(PermissionRequest $request, Role $role)
    {
        try {
            return new RoleResource($this->permissionService->update($request, $role));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
