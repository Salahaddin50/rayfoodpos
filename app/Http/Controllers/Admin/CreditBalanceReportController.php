<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Services\UserService;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\PaginateRequest;
use App\Exports\CreditBalanceReportExport;
use Illuminate\Routing\Controllers\Middleware;
use App\Http\Resources\CreditBalanceUserResource;

class CreditBalanceReportController extends AdminController
{

    private UserService $userService;

    public function __construct(UserService $userService)
    {
        parent::__construct();
        $this->userService = $userService;
    }

    public static function middleware(): array
    {
        return [
            new Middleware('permission:credit-balance-report', only: ['index', 'export']),
        ];
    }

    public function index(PaginateRequest $request): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return CreditBalanceUserResource::collection($this->userService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function export(PaginateRequest $request): \Illuminate\Http\Response | \Symfony\Component\HttpFoundation\BinaryFileResponse | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return Excel::download(new CreditBalanceReportExport($this->userService, $request), 'Credit-balance-Report.xlsx');
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
