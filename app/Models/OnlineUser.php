<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use App\Support\WhatsAppNormalizer;
use Illuminate\Database\Eloquent\Model;

class OnlineUser extends Model
{
    protected $table = 'online_users';

    protected $fillable = [
        'branch_id',
        'whatsapp',
        'location',
        'campaign_id',
        'campaign_joined_at',
        'last_order_id',
        'last_order_at',
    ];

    protected $casts = [
        'id'                 => 'integer',
        'branch_id'          => 'integer',
        'campaign_id'        => 'integer',
        'campaign_joined_at' => 'datetime',
        'last_order_id'      => 'integer',
        'last_order_at'      => 'datetime',
    ];

    public function campaign(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id', 'id');
    }

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope(new BranchScope());
    }

    public function setWhatsappAttribute($value): void
    {
        $this->attributes['whatsapp'] = WhatsAppNormalizer::normalize($value);
    }

    /**
     * Get campaign progress data for admin display
     */
    public function getCampaignProgressAttribute(): ?array
    {
        try {
            if (!$this->campaign_id || !$this->campaign) {
                return null;
            }

            // Ensure freeItem and category are loaded (with error handling)
            try {
                if (!$this->campaign->relationLoaded('freeItem')) {
                    $this->campaign->load('freeItem.category');
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to load freeItem relationship', [
                    'campaign_id' => $this->campaign_id,
                    'error' => $e->getMessage(),
                ]);
            }
            
            $campaign = $this->campaign;
        
        // Only calculate for item-type campaigns
        if ($campaign->type != \App\Enums\CampaignType::ITEM) {
            return [
                'type' => 'percentage',
                'discount_value' => (float) $campaign->discount_value,
                'message' => 'Percentage discount campaign',
            ];
        }

        // Count eligible orders
        $completedStatuses = [
            \App\Enums\OrderStatus::ACCEPT,
            \App\Enums\OrderStatus::PREPARING,
            \App\Enums\OrderStatus::PREPARED,
            \App\Enums\OrderStatus::OUT_FOR_DELIVERY,
            \App\Enums\OrderStatus::DELIVERED,
        ];

        $ordersQuery = \App\Models\Order::withoutGlobalScopes()
            ->where('branch_id', $this->branch_id)
            ->where(function ($query) {
                $query->where('whatsapp_number', 'LIKE', '%' . substr($this->whatsapp, -9))
                    ->orWhere('whatsapp_number', $this->whatsapp);
            })
            ->whereIn('status', $completedStatuses);

        // Filter by join date
        if ($this->campaign_joined_at) {
            $ordersQuery->where('order_datetime', '>=', $this->campaign_joined_at);
        } elseif ($campaign->start_date) {
            $ordersQuery->where('order_datetime', '>=', $campaign->start_date);
        }

        // Filter by end date
        if ($campaign->end_date) {
            $ordersQuery->where('order_datetime', '<=', $campaign->end_date . ' 23:59:59');
        }

        // Filter by category if free item exists
        if ($campaign->free_item_id) {
            // Try to get freeItem from relationship first, then fallback to query
            $freeItem = $campaign->freeItem ?? \App\Models\Item::with('category')->find($campaign->free_item_id);
            
            if ($freeItem && $freeItem->item_category_id) {
                $ordersQuery->whereHas('orderItems', function($q) use ($freeItem) {
                    $q->whereHas('item', function($itemQuery) use ($freeItem) {
                        $itemQuery->where('item_category_id', $freeItem->item_category_id);
                    });
                });
                
                \Log::info('Campaign progress - Filtering by category', [
                    'free_item_id' => $freeItem->id,
                    'category_id' => $freeItem->item_category_id,
                    'category_name' => $freeItem->category->name ?? 'N/A',
                ]);
            } else {
                \Log::warning('Campaign has free_item_id but item not found or has no category', [
                    'campaign_id' => $campaign->id,
                    'free_item_id' => $campaign->free_item_id,
                ]);
            }
        }

        $orderCount = $ordersQuery->count();
        $requiredPurchases = $campaign->required_purchases ?? 8;
        
        // Debug logging for campaign progress calculation (only in development)
        // Commented out to prevent potential serialization issues
        // if (config('app.debug')) {
        //     try {
        //         \Log::info('Campaign progress calculation (OnlineUser model)', [
        //             'online_user_id' => $this->id,
        //             'whatsapp' => $this->whatsapp,
        //             'campaign_id' => $campaign->id,
        //             'campaign_joined_at' => $this->campaign_joined_at?->format('Y-m-d H:i:s'),
        //             'branch_id' => $this->branch_id,
        //             'order_count' => $orderCount,
        //             'required_purchases' => $requiredPurchases,
        //         ]);
        //     } catch (\Exception $logError) {
        //         // Ignore logging errors
        //     }
        // }

        // Count redeemed rewards
        $redeemedCount = \App\Models\Order::withoutGlobalScopes()
            ->where('branch_id', $this->branch_id)
            ->where('campaign_id', $campaign->id)
            ->whereNotNull('campaign_redeem_free_item_id')
            ->where(function ($query) {
                $query->where('whatsapp_number', 'LIKE', '%' . substr($this->whatsapp, -9))
                    ->orWhere('whatsapp_number', $this->whatsapp);
            })
            ->count();

        $earnedRewards = (int) floor($orderCount / $requiredPurchases);
        $rewardsAvailable = max(0, $earnedRewards - $redeemedCount);

        // Check if completed
        $isCompleted = false;
        try {
            $isCompleted = \App\Models\CampaignCompletion::withoutGlobalScopes()
                ->where('campaign_id', $campaign->id)
                ->where('branch_id', $this->branch_id)
                ->where('whatsapp', $this->whatsapp)
                ->exists();
        } catch (\Exception $e) {
            // Table might not exist
        }

        // Auto-mark as completed if progress reaches required purchases
        if (!$isCompleted && $orderCount >= $requiredPurchases && $orderCount > 0) {
            $isCompleted = true;
        }

        return [
            'type' => 'item',
            'current_progress' => $orderCount,
            'required_purchases' => $requiredPurchases,
            'earned_rewards' => $earnedRewards,
            'redeemed_count' => $redeemedCount,
            'rewards_available' => $rewardsAvailable,
            'is_completed' => $isCompleted,
            'campaign_joined_at' => $this->campaign_joined_at?->format('Y-m-d H:i:s'),
            'free_item' => $campaign->freeItem ? [
                'id' => $campaign->freeItem->id,
                'name' => $campaign->freeItem->name,
                'category_name' => ($campaign->freeItem->category ?? null)?->name ?? null,
            ] : null,
        ];
        } catch (\Exception $e) {
            \Log::error('Error calculating campaign progress', [
                'online_user_id' => $this->id,
                'campaign_id' => $this->campaign_id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            // Return null on error to prevent breaking the API
            return null;
        }
    }
}


