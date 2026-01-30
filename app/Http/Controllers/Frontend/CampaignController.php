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

            $whatsapp = WhatsAppNormalizer::normalize($request->phone);
            if ($whatsapp === '') {
                return response()->json([
                    'status'  => false,
                    'message' => 'Invalid phone number',
                ], 422);
            }

            // Find or create online user
            $onlineUser = OnlineUser::withoutGlobalScopes()
                ->where('branch_id', $request->branch_id)
                ->where('whatsapp', $whatsapp)
                ->first();

            if (!$onlineUser) {
                $onlineUser = OnlineUser::create([
                    'branch_id' => $request->branch_id,
                    'whatsapp'  => $whatsapp,
                ]);
            }

            // Link to campaign
            $onlineUser->update(['campaign_id' => $campaign->id]);

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
                        'campaign_name' => $campaign->name,
                        'type'          => 'percentage',
                        'message'       => 'Please approach the branch for your discount.',
                    ],
                ]);
            }

            // Count orders within campaign period
            $ordersQuery = Order::withoutGlobalScopes()
                ->where('branch_id', $request->branch_id)
                ->where(function ($query) use ($whatsapp) {
                    // Orders store customer contact as whatsapp_number (not phone)
                    $query->where('whatsapp_number', 'LIKE', '%' . substr($whatsapp, -9))
                        ->orWhere('whatsapp_number', $whatsapp);
                })
                ->whereIn('status', [5, 10, 15]); // Completed statuses

            if ($campaign->start_date) {
                $ordersQuery->where('created_at', '>=', $campaign->start_date);
            }
            if ($campaign->end_date) {
                $ordersQuery->where('created_at', '<=', $campaign->end_date);
            }

            $orderCount = $ordersQuery->count();
            $requiredPurchases = $campaign->required_purchases ?? 8;
            $progress = min($orderCount, $requiredPurchases);
            $rewardsAvailable = floor($orderCount / $requiredPurchases);

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
