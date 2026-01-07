<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Services\RoleService;
use App\Http\Requests\RoleRequest;
use Spatie\Permission\Models\Role;
use App\Http\Resources\RoleResource;
use App\Http\Requests\PaginateRequest;
use Illuminate\Routing\Controllers\Middleware;

class RoleController extends AdminController
{
    private RoleService $roleService;

    public function __construct(RoleService $roleService)
    {
        parent::__construct();
        $this->roleService = $roleService;
    }

    public static function middleware(): array
    {
        return [
            new Middleware('permission:settings', only: ['show', 'store', 'update', 'destroy']),
            new Middleware('permission:settings|employees', only: ['index']),
        ];
    }

    public function index(PaginateRequest $request)
    {
        try {
            return RoleResource::collection($this->roleService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(Role $role): RoleResource | \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new RoleResource($this->roleService->show($role));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(RoleRequest $request)
    {
        try {
            return new RoleResource($this->roleService->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(RoleRequest $request, Role $role)
    {
        try {
            return new RoleResource($this->roleService->update($request, $role));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(Role $role)
    {
        try {
            $this->roleService->destroy($role);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
