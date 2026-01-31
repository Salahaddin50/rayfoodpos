<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\CampaignType;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\OnlineUser;
use App\Models\Order;
use App\Support\WhatsAppNormalizer;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Enums\OrderStatus;

class CampaignController extends Controller
{
    /**
     * List active campaigns for frontend
     */
    public function index(Request $request)
    {
        try {
            $campaigns = Campaign::with(['freeItem:id,name'])->where('status', 5) // Active status
                ->where(function ($query) {
                    // Only filter by end_date - show upcoming campaigns too
                    $query->whereNull('end_date')
                        ->orWhere('end_date', '>=', now());
                })
                ->orderBy('name')
                ->get()
                ->map(function ($campaign) {
                    // Format discount value nicely (remove trailing zeros)
                    $discountValue = $campaign->discount_value;
                    if ($discountValue == floor($discountValue)) {
                        $discountValue = (int) $discountValue;
                    }
                    
                    return [
                        'id'                 => $campaign->id,
                        'name'               => $campaign->name,
                        'description'        => $campaign->description,
                        'type'               => $campaign->type,
                        'type_name'          => $campaign->type == CampaignType::PERCENTAGE ? 'percentage' : 'item',
                        'discount_value'     => $discountValue,
                        'free_item_id'       => $campaign->free_item_id,
                        'free_item'          => $campaign->freeItem ? [
                            'id'   => $campaign->freeItem->id,
                            'name' => $campaign->freeItem->name,
                        ] : null,
                        'required_purchases' => $campaign->required_purchases,
                        'start_date'         => $campaign->start_date,
                        'end_date'           => $campaign->end_date,
                    ];
                });

            return response()->json([
                'status' => true,
                'data'   => $campaigns,
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'status'  => false,
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    /**
     * Join a campaign (link user to campaign)
     */
    public function join(Request $request)
    {
        try {
            $request->validate([
                'campaign_id' => 'required|exists:campaigns,id',
                'phone'       => 'required|string|max:32',
                'branch_id'   => 'required|exists:branches,id',
            ]);

            $campaign = Campaign::find($request->campaign_id);

            // Only allow joining item-type campaigns
            if ($campaign->type == CampaignType::PERCENTAGE) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Please approach the branch to join this campaign.',
                ], 422);
            }

            // Check if campaign has started
            $now = now();
            if ($campaign->start_date && $campaign->start_date > $now) {
                $daysUntilStart = $now->diffInDays($campaign->start_date, false);
                $hoursUntilStart = $now->diffInHours($campaign->start_date, false);
                
                if ($daysUntilStart >= 1) {
                    $message = sprintf('Campaign will start in %d day%s.', 
                        ceil($daysUntilStart), 
                        ceil($daysUntilStart) > 1 ? 's' : ''
                    );
                } else {
                    $message = sprintf('Campaign will start in %d hour%s.', 
                        ceil($hoursUntilStart), 
                        ceil($hoursUntilStart) > 1 ? 's' : ''
                    );
                }
                
                return response()->json([
                    'status'  => false,
                    'message' => $message,
                    'data'    => [
                        'start_date' => $campaign->start_date->format('Y-m-d H:i:s'),
                    ],
                ], 422);
            }

            // Check if campaign has ended
            if ($campaign->end_date && $campaign->end_date < $now) {
                return response()->json([
                    'status'  => false,
                    'message' => 'This campaign has ended.',
                ], 422);
            }

            $whatsapp = WhatsAppNormalizer::normalize($request->phone);
            if ($whatsapp === '') {
                return response()->json([
                    'status'  => false,
                    'message' => 'Invalid phone number',
                ], 422);
            }

            // Check if user already completed this campaign
            $alreadyCompleted = \App\Models\CampaignCompletion::where('campaign_id', $campaign->id)
                ->where('branch_id', $request->branch_id)
                ->where('whatsapp', $whatsapp)
                ->exists();

            if ($alreadyCompleted) {
                return response()->json([
                    'status'  => false,
                    'message' => 'You have already completed this campaign and cannot rejoin it.',
                ], 422);
            }

            // Check if user is already in another ITEM campaign
            $onlineUser = OnlineUser::withoutGlobalScopes()
                ->where('branch_id', $request->branch_id)
                ->where('whatsapp', $whatsapp)
                ->whereNotNull('campaign_id')
                ->with('campaign')
                ->first();

            if ($onlineUser && $onlineUser->campaign) {
                // If already in THIS campaign, just return success
                if ($onlineUser->campaign_id == $campaign->id) {
                    return response()->json([
                        'status'  => true,
                        'message' => 'You are already enrolled in this campaign!',
                        'data'    => [
                            'campaign_name'      => $campaign->name,
                            'required_purchases' => $campaign->required_purchases,
                        ],
                    ]);
                }

                // If in a different ITEM campaign, prevent joining
                if ($onlineUser->campaign->type == CampaignType::ITEM) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'You are already enrolled in "' . $onlineUser->campaign->name . '". Complete it first before joining another campaign.',
                    ], 422);
                }
            }

            // Find or create online user
            if (!$onlineUser) {
                $onlineUser = OnlineUser::create([
                    'branch_id' => $request->branch_id,
                    'whatsapp'  => $whatsapp,
                ]);
            }

            // Link to campaign with join timestamp (NOW, not campaign start date)
            // This ensures orders are counted from the moment they join
            $onlineUser->update([
                'campaign_id'        => $campaign->id,
                'campaign_joined_at' => now(), // Always use current time, even if campaign started earlier
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Successfully joined the campaign!',
                'data'    => [
                    'campaign_name'      => $campaign->name,
                    'required_purchases' => $campaign->required_purchases,
                ],
            ]);
        } catch (ValidationException $exception) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed',
                'errors'  => $exception->errors(),
            ], 422);
        } catch (Exception $exception) {
            return response()->json([
                'status'  => false,
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    /**
     * Get campaign progress for a user
     */
    public function progress(Request $request)
    {
        try {
            // Log incoming request for debugging
            \Log::info('Campaign progress request', [
                'phone' => $request->phone,
                'branch_id' => $request->branch_id,
                'all' => $request->all()
            ]);

            $request->validate([
                'phone'     => 'required|string|max:32',
                'branch_id' => 'required|exists:branches,id',
            ]);

            $whatsapp = WhatsAppNormalizer::normalize($request->phone);
            \Log::info('Normalized WhatsApp', ['original' => $request->phone, 'normalized' => $whatsapp]);
            
            if ($whatsapp === '') {
                return response()->json([
                    'status'  => false,
                    'message' => 'Invalid phone number',
                ], 422);
            }

            // Find online user with campaign
            $onlineUser = OnlineUser::withoutGlobalScopes()
                ->where('branch_id', $request->branch_id)
                ->where('whatsapp', $whatsapp)
                ->whereNotNull('campaign_id')
                ->with('campaign')
                ->first();

            \Log::info('OnlineUser lookup', [
                'branch_id' => $request->branch_id,
                'whatsapp' => $whatsapp,
                'found' => $onlineUser ? true : false,
                'campaign_id' => $onlineUser?->campaign_id,
            ]);

            if (!$onlineUser || !$onlineUser->campaign) {
                return response()->json([
                    'status' => true,
                    'data'   => null,
                ]);
            }

            $campaign = $onlineUser->campaign;

            // Only show progress for item-type campaigns
            if ($campaign->type == CampaignType::PERCENTAGE) {
                return response()->json([
                    'status' => true,
                    'data'   => [
                        'campaign_id'   => $campaign->id,
                        'campaign_name' => $campaign->name,
                        'type'          => 'percentage',
                        'discount_value' => (float) $campaign->discount_value,
                        'message'       => 'Please approach the branch for your discount.',
                    ],
                ]);
            }

            // Count PAID/COMPLETED orders within campaign period
            // Include: ACCEPT (4), PREPARING (7), PREPARED (8), OUT_FOR_DELIVERY (10), DELIVERED (13)
            $completedStatuses = [
                OrderStatus::ACCEPT,
                OrderStatus::PREPARING,
                OrderStatus::PREPARED,
                OrderStatus::OUT_FOR_DELIVERY,
                OrderStatus::DELIVERED,
            ];

            $ordersQuery = Order::withoutGlobalScopes()
                ->where('branch_id', $request->branch_id)
                ->where(function ($query) use ($whatsapp) {
                    // Orders store customer contact as whatsapp_number (not phone)
                    $query->where('whatsapp_number', 'LIKE', '%' . substr($whatsapp, -9))
                        ->orWhere('whatsapp_number', $whatsapp);
                })
                ->whereIn('status', $completedStatuses);

            // IMPORTANT: Only count orders placed AFTER user joined the campaign
            // This prevents orders from previous campaigns from carrying over
            if ($onlineUser->campaign_joined_at) {
                $ordersQuery->where('order_datetime', '>=', $onlineUser->campaign_joined_at);
                \Log::info('Using campaign_joined_at for filtering', [
                    'campaign_joined_at' => $onlineUser->campaign_joined_at,
                ]);
            } elseif ($campaign->start_date) {
                // Fallback to campaign start date if no join date recorded (legacy users)
                $ordersQuery->where('order_datetime', '>=', $campaign->start_date);
                \Log::warning('campaign_joined_at is NULL, falling back to campaign start_date', [
                    'campaign_start_date' => $campaign->start_date,
                ]);
            } else {
                \Log::warning('No campaign_joined_at or campaign start_date - counting all orders!');
            }

            // Filter by campaign end date using order_datetime (not created_at)
            if ($campaign->end_date) {
                $ordersQuery->where('order_datetime', '<=', $campaign->end_date . ' 23:59:59');
            }

            $orderCount = $ordersQuery->count();
            $requiredPurchases = $campaign->required_purchases ?? 8;
            
            // Debug: Get actual orders for logging
            $foundOrders = $ordersQuery->get(['id', 'order_serial_no', 'whatsapp_number', 'order_datetime', 'status']);
            
            // Progress is how many orders toward next reward (modulo)
            $progressTowardNext = $orderCount % $requiredPurchases;
            // But if they just completed a set, show full progress
            $progress = $progressTowardNext == 0 && $orderCount > 0 ? $requiredPurchases : $progressTowardNext;

            \Log::info('Campaign order count', [
                'whatsapp' => $whatsapp,
                'whatsapp_last_9' => substr($whatsapp, -9),
                'branch_id' => $request->branch_id,
                'campaign_id' => $campaign->id,
                'campaign_joined_at' => $onlineUser->campaign_joined_at,
                'campaign_start_date' => $campaign->start_date,
                'campaign_end_date' => $campaign->end_date,
                'order_count' => $orderCount,
                'required_purchases' => $requiredPurchases,
                'found_orders' => $foundOrders->map(function($o) {
                    return [
                        'id' => $o->id,
                        'serial' => $o->order_serial_no,
                        'whatsapp' => $o->whatsapp_number,
                        'datetime' => $o->order_datetime,
                        'status' => $o->status,
                    ];
                })->toArray(),
            ]);

            // Rewards already redeemed (stored on orders)
            $redeemedCount = Order::withoutGlobalScopes()
                ->where('branch_id', $request->branch_id)
                ->where('campaign_id', $campaign->id)
                ->whereNotNull('campaign_redeem_free_item_id')
                ->where(function ($query) use ($whatsapp) {
                    $query->where('whatsapp_number', 'LIKE', '%' . substr($whatsapp, -9))
                        ->orWhere('whatsapp_number', $whatsapp);
                })
                ->count();

            $earnedRewards = (int) floor($orderCount / $requiredPurchases);
            $rewardsAvailable = max(0, $earnedRewards - $redeemedCount);

            return response()->json([
                'status' => true,
                'data'   => [
                    'campaign_id'        => $campaign->id,
                    'campaign_name'      => $campaign->name,
                    'type'               => 'item',
                    'required_purchases' => $requiredPurchases,
                    'current_progress'   => $progress,
                    'total_orders'       => $orderCount,
                    'rewards_available'  => $rewardsAvailable,
                    'redeemed_count'     => $redeemedCount,
                    'free_item'          => $campaign->freeItem ? [
                        'id'   => $campaign->freeItem->id,
                        'name' => $campaign->freeItem->name,
                    ] : null,
                    'is_complete'        => $progress >= $requiredPurchases,
                ],
            ]);
        } catch (ValidationException $exception) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed',
                'errors'  => $exception->errors(),
            ], 422);
        } catch (Exception $exception) {
            return response()->json([
                'status'  => false,
                'message' => $exception->getMessage(),
            ], 422);
        }
    }
}
