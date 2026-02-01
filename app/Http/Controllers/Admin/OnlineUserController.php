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
                    // Adjust order count by setting a manual adjustment
                    // We'll store this in a JSON field or use a workaround
                    // For now, we'll calculate the required join date to achieve the desired count
                    $desiredOrderCount = $request->input('order_count', 0);
                    if ($desiredOrderCount < 0) {
                        return response(['status' => false, 'message' => 'Order count cannot be negative'], 422);
                    }
                    
                    if (!$onlineUser->campaign_id || !$onlineUser->campaign) {
                        return response(['status' => false, 'message' => 'User is not enrolled in a campaign'], 422);
                    }
                    
                    $campaign = $onlineUser->campaign;
                    
                    // Calculate how many orders the user currently has
                    $completedStatuses = [
                        \App\Enums\OrderStatus::ACCEPT,
                        \App\Enums\OrderStatus::PREPARING,
                        \App\Enums\OrderStatus::PREPARED,
                        \App\Enums\OrderStatus::OUT_FOR_DELIVERY,
                        \App\Enums\OrderStatus::DELIVERED,
                    ];
                    
                    $currentOrdersQuery = \App\Models\Order::withoutGlobalScopes()
                        ->where('branch_id', $onlineUser->branch_id)
                        ->where(function ($query) use ($onlineUser) {
                            $query->where('whatsapp_number', 'LIKE', '%' . substr($onlineUser->whatsapp, -9))
                                ->orWhere('whatsapp_number', $onlineUser->whatsapp);
                        })
                        ->whereIn('status', $completedStatuses);
                    
                    if ($onlineUser->campaign_joined_at) {
                        $currentOrdersQuery->where('order_datetime', '>=', $onlineUser->campaign_joined_at);
                    }
                    
                    if ($campaign->end_date) {
                        $currentOrdersQuery->where('order_datetime', '<=', $campaign->end_date . ' 23:59:59');
                    }
                    
                    // Filter by category if needed
                    if ($campaign->free_item_id) {
                        $freeItem = \App\Models\Item::with('category')->find($campaign->free_item_id);
                        if ($freeItem && $freeItem->item_category_id) {
                            $currentOrdersQuery->whereHas('orderItems', function($q) use ($freeItem) {
                                $q->whereHas('item', function($itemQuery) use ($freeItem) {
                                    $itemQuery->where('item_category_id', $freeItem->item_category_id);
                                });
                            });
                        }
                    }
                    
                    $currentOrderCount = $currentOrdersQuery->count();
                    $difference = $desiredOrderCount - $currentOrderCount;
                    
                    if ($difference == 0) {
                        return response([
                            'status' => true,
                            'message' => 'Order count is already at the desired value.',
                        ]);
                    }
                    
                    // Adjust the join date to achieve the desired count
                    // If we want more orders, move join date backwards
                    // If we want fewer orders, move join date forwards
                    // We'll find the date that gives us the desired count
                    if ($difference > 0) {
                        // Need more orders - move join date backwards
                        // Find the date where we have exactly the desired count
                        $orders = $currentOrdersQuery->orderBy('order_datetime', 'asc')->get();
                        if ($orders->count() >= $desiredOrderCount) {
                            // Set join date to the date of the order that would give us the desired count
                            $targetOrder = $orders[$desiredOrderCount - 1] ?? null;
                            if ($targetOrder) {
                                $newJoinDate = \Carbon\Carbon::parse($targetOrder->order_datetime)->subSecond();
                                $onlineUser->update(['campaign_joined_at' => $newJoinDate]);
                            }
                        } else {
                            // Not enough orders exist - set join date to a past date
                            $onlineUser->update(['campaign_joined_at' => now()->subDays(30)]);
                        }
                    } else {
                        // Need fewer orders - move join date forwards
                        // Find the date where we have exactly the desired count
                        $orders = $currentOrdersQuery->orderBy('order_datetime', 'desc')->get();
                        if ($orders->count() > abs($difference)) {
                            // Set join date to exclude the extra orders
                            $targetOrder = $orders[abs($difference) - 1] ?? null;
                            if ($targetOrder) {
                                $newJoinDate = \Carbon\Carbon::parse($targetOrder->order_datetime)->addSecond();
                                $onlineUser->update(['campaign_joined_at' => $newJoinDate]);
                            }
                        } else {
                            // Set to now to reset
                            $onlineUser->update(['campaign_joined_at' => now()]);
                        }
                    }
                    
                    return response([
                        'status' => true,
                        'message' => "Order count adjusted to {$desiredOrderCount}.",
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


