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
                    // Reset campaign join date to now (starts counting from now)
                    $onlineUser->update([
                        'campaign_joined_at' => now(),
                    ]);
                    return response([
                        'status' => true,
                        'message' => 'Campaign progress reset successfully. Order count will start from now.',
                    ]);

                case 'adjust':
                    // Adjust order count by calculating the required join date
                    $desiredOrderCount = $request->input('order_count', 0);
                    if ($desiredOrderCount < 0) {
                        return response(['status' => false, 'message' => 'Order count cannot be negative'], 422);
                    }
                    
                    if (!$onlineUser->campaign_id || !$onlineUser->campaign) {
                        return response(['status' => false, 'message' => 'User is not enrolled in a campaign'], 422);
                    }
                    
                    $campaign = $onlineUser->campaign;
                    
                    // Get ALL eligible orders (without join date filter) to find the right date
                    $completedStatuses = [
                        \App\Enums\OrderStatus::ACCEPT,
                        \App\Enums\OrderStatus::PREPARING,
                        \App\Enums\OrderStatus::PREPARED,
                        \App\Enums\OrderStatus::OUT_FOR_DELIVERY,
                        \App\Enums\OrderStatus::DELIVERED,
                    ];
                    
                    // Query ALL orders (no join date filter yet)
                    $allOrdersQuery = \App\Models\Order::withoutGlobalScopes()
                        ->where('branch_id', $onlineUser->branch_id)
                        ->where(function ($query) use ($onlineUser) {
                            $query->where('whatsapp_number', 'LIKE', '%' . substr($onlineUser->whatsapp, -9))
                                ->orWhere('whatsapp_number', $onlineUser->whatsapp);
                        })
                        ->whereIn('status', $completedStatuses);
                    
                    // Filter by campaign end date if exists
                    if ($campaign->end_date) {
                        $allOrdersQuery->where('order_datetime', '<=', $campaign->end_date . ' 23:59:59');
                    }
                    
                    // Filter by category if needed
                    if ($campaign->free_item_id) {
                        $freeItem = \App\Models\Item::with('category')->find($campaign->free_item_id);
                        if ($freeItem && $freeItem->item_category_id) {
                            $allOrdersQuery->whereHas('orderItems', function($q) use ($freeItem) {
                                $q->whereHas('item', function($itemQuery) use ($freeItem) {
                                    $itemQuery->where('item_category_id', $freeItem->item_category_id);
                                });
                            });
                        }
                    }
                    
                    // Get all orders sorted by date
                    $allOrders = $allOrdersQuery->orderBy('order_datetime', 'asc')->get();
                    
                    if ($allOrders->count() == 0) {
                        // No orders exist - set join date to now
                        $onlineUser->campaign_joined_at = now();
                        $onlineUser->save();
                        return response([
                            'status' => true,
                            'message' => "No eligible orders found. Join date reset to now.",
                        ]);
                    }
                    
                    if ($desiredOrderCount == 0) {
                        // Set join date to now (no orders counted)
                        $onlineUser->campaign_joined_at = now();
                        $onlineUser->save();
                        return response([
                            'status' => true,
                            'message' => "Order count set to 0. Join date reset to now.",
                        ]);
                    }
                    
                    if ($desiredOrderCount > $allOrders->count()) {
                        // Desired count is more than available orders
                        // Set join date to the earliest order date
                        $earliestOrder = $allOrders->first();
                        $newJoinDate = \Carbon\Carbon::parse($earliestOrder->order_datetime)->subSecond();
                        $onlineUser->campaign_joined_at = $newJoinDate;
                        $saved = $onlineUser->save();
                        
                        if (!$saved) {
                            \Log::error('Failed to save campaign_joined_at', [
                                'online_user_id' => $onlineUser->id,
                                'new_join_date' => $newJoinDate,
                            ]);
                            return response(['status' => false, 'message' => 'Failed to save join date'], 422);
                        }
                        
                        \Log::info('Adjusted join date - desired count exceeds available orders', [
                            'desired' => $desiredOrderCount,
                            'available' => $allOrders->count(),
                            'new_join_date' => $newJoinDate,
                            'saved' => $saved,
                        ]);
                    } else {
                        // Find the order that would give us exactly the desired count
                        // If we want 1 order, we need join date before the 1st order
                        // If we want 2 orders, we need join date before the 2nd order, etc.
                        $targetOrderIndex = $desiredOrderCount - 1;
                        $targetOrder = $allOrders[$targetOrderIndex] ?? null;
                        
                        if ($targetOrder) {
                            // Set join date to just before this order
                            $newJoinDate = \Carbon\Carbon::parse($targetOrder->order_datetime)->subSecond();
                            
                            // Update using save() to ensure it's persisted
                            $onlineUser->campaign_joined_at = $newJoinDate;
                            $saved = $onlineUser->save();
                            
                            if (!$saved) {
                                \Log::error('Failed to save campaign_joined_at', [
                                    'online_user_id' => $onlineUser->id,
                                    'new_join_date' => $newJoinDate,
                                ]);
                                return response(['status' => false, 'message' => 'Failed to save join date'], 422);
                            }
                            
                            \Log::info('Adjusted join date for order count', [
                                'desired_count' => $desiredOrderCount,
                                'target_order_id' => $targetOrder->id,
                                'target_order_date' => $targetOrder->order_datetime,
                                'new_join_date' => $newJoinDate,
                                'saved' => $saved,
                            ]);
                        } else {
                            // Fallback - set to now
                            $onlineUser->campaign_joined_at = now();
                            $onlineUser->save();
                        }
                    }
                    
                    // Verify the update was saved
                    $onlineUser->refresh();
                    
                    // Log the update for debugging
                    \Log::info('Campaign join date adjusted', [
                        'online_user_id' => $onlineUser->id,
                        'whatsapp' => $onlineUser->whatsapp,
                        'campaign_id' => $onlineUser->campaign_id,
                        'new_campaign_joined_at' => $onlineUser->campaign_joined_at,
                        'desired_order_count' => $desiredOrderCount,
                    ]);
                    
                    return response([
                        'status' => true,
                        'message' => "Order count adjusted to {$desiredOrderCount}. Join date updated.",
                        'data' => [
                            'campaign_joined_at' => $onlineUser->campaign_joined_at?->format('Y-m-d H:i:s'),
                        ],
                    ]);

                case 'remove':
                    // Remove campaign assignment
                    $onlineUser->update([
                        'campaign_id' => null,
                        'campaign_joined_at' => null,
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


