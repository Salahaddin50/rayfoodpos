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
            $permissions     = Permission::orderBy('id')->get();
            $rolePermissions = Permission::join(
                "role_has_permissions",
                "role_has_permissions.permission_id",
                "=",
                "permissions.id"
            )->where("role_has_permissions.role_id", $role->id)->get()->pluck('name', 'id');
            $permissions     = AppLibrary::permissionWithAccess($permissions, $rolePermissions);
            
            // Convert to array BEFORE building hierarchy
            $permissionsArray = [];
            foreach ($permissions as $p) {
                $permissionsArray[] = [
                    'id' => $p->id,
                    'title' => $p->title,
                    'name' => $p->name,
                    'guard_name' => $p->guard_name,
                    'url' => $p->url,
                    'parent' => $p->parent,
                    'created_at' => $p->created_at,
                    'updated_at' => $p->updated_at,
                    'access' => $p->access ?? false,
                ];
            }
            
            $permissions     = AppLibrary::numericToAssociativeArrayBuilder($permissionsArray);

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
                
                // Ensure children are in consistent order: Create, Edit, Delete, Show
                if (!empty($p['children']) && is_array($p['children'])) {
                    usort($p['children'], function($a, $b) {
                        $order = ['create' => 1, 'edit' => 2, 'delete' => 3, 'show' => 4];
                        $aType = 5; $bType = 5;
                        foreach ($order as $suffix => $priority) {
                            if (str_ends_with($a['name'] ?? '', '_' . $suffix)) $aType = $priority;
                            if (str_ends_with($b['name'] ?? '', '_' . $suffix)) $bType = $priority;
                        }
                        return $aType <=> $bType;
                    });
                }
                
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
