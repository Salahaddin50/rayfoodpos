<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OnlineUserResource;
use App\Models\OnlineUser;
use App\Services\OnlineUserService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class OnlineUserController extends Controller implements HasMiddleware
{
    public function __construct(private readonly OnlineUserService $onlineUserService)
    {
    }

    public static function middleware(): array
    {
        return [
            // Only admins can access this section (per requirement).
            new Middleware('permission:online_users', only: ['index']),
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
}


