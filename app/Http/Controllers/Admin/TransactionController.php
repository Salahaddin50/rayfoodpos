<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Exports\TransactionExport;
use App\Services\TransactionService;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\TransactionResource;
use Illuminate\Routing\Controllers\Middleware;

class TransactionController extends AdminController
{
    public TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        parent::__construct();
        $this->transactionService = $transactionService;
    }

    public static function middleware(): array
    {
        return [
            new Middleware('permission:transactions', only: ['index', 'export']),
        ];
    }

    public function index(PaginateRequest $request)
    {
        try {
            return TransactionResource::collection($this->transactionService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function export(PaginateRequest $request): \Illuminate\Http\Response | \Symfony\Component\HttpFoundation\BinaryFileResponse | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return Excel::download(new TransactionExport($this->transactionService, $request), 'Transaction.xlsx');
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
