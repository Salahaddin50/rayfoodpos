<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OnlineUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'branch_id'     => $this->branch_id,
            'whatsapp'      => $this->whatsapp,
            'location'      => $this->location,
            'campaign_id'   => $this->campaign_id,
            'campaign_name' => $this->campaign ? $this->campaign->name : null,
            'campaign_joined_at' => $this->campaign_joined_at?->format('Y-m-d H:i:s'),
            'campaign_progress' => $this->campaign_progress,
            'last_order_id' => $this->last_order_id,
            'last_order_at' => $this->last_order_at,
        ];
    }
}


