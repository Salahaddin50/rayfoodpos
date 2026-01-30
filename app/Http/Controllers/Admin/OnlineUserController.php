<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportFileRequest;
use App\Http\Requests\OnlineUserRequest;
use App\Http\Resources\OnlineUserResource;
use App\Exports\OnlineUserExport;
use App\Exports\OnlineUserSampleExport;
use App\Imports\OnlineUserImport;
use App\Models\OnlineUser;
use App\Services\OnlineUserService;
use App\Support\WhatsAppNormalizer;
use App\Traits\DefaultAccessModelTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Maatwebsite\Excel\Facades\Excel;

class OnlineUserController extends Controller implements HasMiddleware
{
    use DefaultAccessModelTrait;

    public function __construct(private readonly OnlineUserService $onlineUserService)
    {
    }

    public static function middleware(): array
    {
        return [
            // Only admins can access this section (per requirement).
            new Middleware('permission:online_users', only: ['index', 'export', 'downloadSample', 'import']),
            new Middleware('permission:online_users_create', only: ['store']),
            new Middleware('permission:online_users_edit', only: ['update']),
            new Middleware('permission:online_users_delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        try {
            // Ensure DB-backed list exists (including dining-table orders when whatsapp is provided)
            $this->onlineUserService->ensureSyncedForCurrentBranch();

            return OnlineUserResource::collection(
                OnlineUser::with('campaign')
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

    public function store(OnlineUserRequest $request)
    {
        try {
            $data = $request->validated();

            $branchId = $this->branch();
            $whatsapp = WhatsAppNormalizer::normalize($data['whatsapp'] ?? null);
            if ($whatsapp === '') {
                return response(['status' => false, 'message' => 'Invalid WhatsApp number'], 422);
            }

            $exists = OnlineUser::where('branch_id', $branchId)->where('whatsapp', $whatsapp)->exists();
            if ($exists) {
                return response(['status' => false, 'message' => 'WhatsApp number already exists'], 422);
            }

            $onlineUser = OnlineUser::create([
                'branch_id'   => $branchId,
                'whatsapp'    => $whatsapp,
                'location'    => $data['location'] ?? null,
                'campaign_id' => $data['campaign_id'] ?? null,
            ]);
            return new OnlineUserResource($onlineUser->load('campaign'));
        } catch (\Throwable $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(OnlineUserRequest $request, OnlineUser $onlineUser)
    {
        try {
            $data = $request->validated();
            $branchId = $this->branch();

            $whatsapp = WhatsAppNormalizer::normalize($data['whatsapp'] ?? null);
            if ($whatsapp === '') {
                return response(['status' => false, 'message' => 'Invalid WhatsApp number'], 422);
            }

            $duplicate = OnlineUser::where('branch_id', $branchId)
                ->where('whatsapp', $whatsapp)
                ->where('id', '!=', $onlineUser->id)
                ->exists();
            if ($duplicate) {
                return response(['status' => false, 'message' => 'WhatsApp number already exists'], 422);
            }

            $onlineUser->update([
                'whatsapp'    => $whatsapp,
                'location'    => $data['location'] ?? null,
                'campaign_id' => $data['campaign_id'] ?? null,
            ]);
            return new OnlineUserResource($onlineUser->fresh()->load('campaign'));
        } catch (\Throwable $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(OnlineUser $onlineUser)
    {
        try {
            $onlineUser->delete();
            return response(['status' => true]);
        } catch (\Throwable $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}


