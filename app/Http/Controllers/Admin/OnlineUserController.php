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

    /**
     * Update campaign progress for an online user
     * Allows admin to adjust order count, reset progress, or remove campaign
     */
    public function updateCampaignProgress(Request $request, OnlineUser $onlineUser)
    {
        try {
            $request->validate([
                'action' => 'required|in:reset,adjust,remove,complete',
                'order_count' => 'nullable|integer|min:0', // For adjust action - sets manual order count
            ]);

            $action = $request->input('action');

            switch ($action) {
                case 'reset':
                    // Reset campaign progress - clear manual count and reset join date to now
                    // Also delete any completion records so frontend doesn't show it as completed
                    try {
                        if ($onlineUser->campaign_id) {
                            \App\Models\CampaignCompletion::withoutGlobalScopes()
                                ->where('campaign_id', $onlineUser->campaign_id)
                                ->where('branch_id', $onlineUser->branch_id)
                                ->where('whatsapp', $onlineUser->whatsapp)
                                ->delete();
                        }
                    } catch (\Exception $e) {
                        \Log::warning('Failed to delete completion record on reset', [
                            'error' => $e->getMessage(),
                            'online_user_id' => $onlineUser->id,
                        ]);
                    }
                    
                    $onlineUser->update([
                        'campaign_joined_at' => now(),
                        'campaign_manual_order_count' => null, // Clear manual override
                    ]);
                    return response([
                        'status' => true,
                        'message' => 'Campaign progress reset successfully. Order count will start from now.',
                    ]);

                case 'adjust':
                    // Directly set manual order count (e.g., set to 5/8)
                    $desiredOrderCount = $request->input('order_count', 0);
                    if ($desiredOrderCount < 0) {
                        return response(['status' => false, 'message' => 'Order count cannot be negative'], 422);
                    }
                    
                    if (!$onlineUser->campaign_id || !$onlineUser->campaign) {
                        return response(['status' => false, 'message' => 'User is not enrolled in a campaign'], 422);
                    }
                    
                    // Directly set the manual order count
                    $onlineUser->campaign_manual_order_count = $desiredOrderCount;
                    $saved = $onlineUser->save();
                    
                    if (!$saved) {
                        \Log::error('Failed to save campaign_manual_order_count', [
                            'online_user_id' => $onlineUser->id,
                            'desired_order_count' => $desiredOrderCount,
                        ]);
                        return response(['status' => false, 'message' => 'Failed to save order count'], 422);
                    }
                    
                    // Verify the update was saved
                    $onlineUser->refresh();
                    
                    \Log::info('Manual order count set', [
                        'online_user_id' => $onlineUser->id,
                        'whatsapp' => $onlineUser->whatsapp,
                        'campaign_id' => $onlineUser->campaign_id,
                        'manual_order_count' => $onlineUser->campaign_manual_order_count,
                    ]);
                    
                    return response([
                        'status' => true,
                        'message' => "Order count set to {$desiredOrderCount}.",
                        'data' => [
                            'campaign_manual_order_count' => $onlineUser->campaign_manual_order_count,
                        ],
                    ]);

                case 'remove':
                    // Remove campaign assignment
                    // Also delete any completion records so frontend doesn't show it as completed
                    $campaignIdToRemove = $onlineUser->campaign_id;
                    try {
                        if ($campaignIdToRemove) {
                            \App\Models\CampaignCompletion::withoutGlobalScopes()
                                ->where('campaign_id', $campaignIdToRemove)
                                ->where('branch_id', $onlineUser->branch_id)
                                ->where('whatsapp', $onlineUser->whatsapp)
                                ->delete();
                        }
                    } catch (\Exception $e) {
                        \Log::warning('Failed to delete completion record on remove', [
                            'error' => $e->getMessage(),
                            'online_user_id' => $onlineUser->id,
                        ]);
                    }
                    
                    $onlineUser->update([
                        'campaign_id' => null,
                        'campaign_joined_at' => null,
                        'campaign_manual_order_count' => null, // Clear manual override
                    ]);
                    return response([
                        'status' => true,
                        'message' => 'Campaign removed from user successfully.',
                    ]);

                case 'complete':
                    // Mark campaign as completed
                    if (!$onlineUser->campaign_id || !$onlineUser->campaign) {
                        return response(['status' => false, 'message' => 'User is not enrolled in a campaign'], 422);
                    }

                    $campaign = $onlineUser->campaign;
                    
                    // Check if already completed
                    try {
                        $alreadyCompleted = \App\Models\CampaignCompletion::withoutGlobalScopes()
                            ->where('campaign_id', $campaign->id)
                            ->where('branch_id', $onlineUser->branch_id)
                            ->where('whatsapp', $onlineUser->whatsapp)
                            ->exists();
                        
                        if ($alreadyCompleted) {
                            return response([
                                'status' => true,
                                'message' => 'Campaign is already marked as completed.',
                            ]);
                        }

                        // Create completion record
                        \App\Models\CampaignCompletion::create([
                            'campaign_id'    => $campaign->id,
                            'branch_id'      => $onlineUser->branch_id,
                            'whatsapp'       => $onlineUser->whatsapp,
                            'completed_at'   => now(),
                            'final_order_id' => null, // No specific order for manual completion
                        ]);

                        // Optionally remove user from campaign (uncomment if desired)
                        // $onlineUser->update([
                        //     'campaign_id'        => null,
                        //     'campaign_joined_at' => null,
                        // ]);

                        return response([
                            'status' => true,
                            'message' => 'Campaign marked as completed successfully.',
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Failed to mark campaign as completed', [
                            'error' => $e->getMessage(),
                            'online_user_id' => $onlineUser->id,
                            'campaign_id' => $campaign->id,
                        ]);
                        return response(['status' => false, 'message' => 'Failed to mark campaign as completed: ' . $e->getMessage()], 422);
                    }

                default:
                    return response(['status' => false, 'message' => 'Invalid action'], 422);
            }
        } catch (\Throwable $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}


