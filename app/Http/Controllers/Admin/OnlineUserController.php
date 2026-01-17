<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportFileRequest;
use App\Http\Resources\OnlineUserResource;
use App\Exports\OnlineUserExport;
use App\Exports\OnlineUserSampleExport;
use App\Imports\OnlineUserImport;
use App\Models\OnlineUser;
use App\Services\OnlineUserService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Maatwebsite\Excel\Facades\Excel;

class OnlineUserController extends Controller implements HasMiddleware
{
    public function __construct(private readonly OnlineUserService $onlineUserService)
    {
    }

    public static function middleware(): array
    {
        return [
            // Only admins can access this section (per requirement).
            new Middleware('permission:online_users', only: ['index', 'export', 'downloadSample', 'import']),
        ];
    }

    public function index(Request $request)
    {
        try {
            // Ensure DB-backed list exists (including dining-table orders when whatsapp is provided)
            $this->onlineUserService->ensureSyncedForCurrentBranch();

            return OnlineUserResource::collection(
                OnlineUser::query()
                    ->orderByDesc('last_order_at')
                    ->get()
            );
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function export()
    {
        try {
            $this->onlineUserService->ensureSyncedForCurrentBranch();
            return Excel::download(new OnlineUserExport(), 'Online Users.xlsx');
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function downloadSample()
    {
        try {
            return Excel::download(new OnlineUserSampleExport(), 'Online Users Import Sample.xlsx');
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function import(ImportFileRequest $request)
    {
        try {
            Excel::import(new OnlineUserImport(), $request->file('file'));
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}


