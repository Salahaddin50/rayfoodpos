<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Services\CompanyService;
use App\Http\Requests\CompanyRequest;
use App\Http\Resources\CompanyResource;
use Illuminate\Routing\Controllers\Middleware;

class CompanyController extends AdminController
{
    public CompanyService $companyService;

    public function __construct(CompanyService $companyService)
    {
        parent::__construct();
        $this->companyService = $companyService;
    }

    public static function middleware(): array
    {
        return [
            new Middleware('permission:settings', only: ['update']),
        ];
    }

    public function index(): \Illuminate\Http\Response | CompanyResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new CompanyResource($this->companyService->list());
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(CompanyRequest $request): \Illuminate\Http\Response | CompanyResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new CompanyResource($this->companyService->update($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
