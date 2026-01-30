<?php

namespace App\Http\Resources;

use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignRegistrationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id'                => $this->id,
            'campaign_id'       => $this->campaign_id,
            'name'              => $this->name,
            'email'             => $this->email,
            'phone'             => $this->phone,
            'verification_code' => $this->verification_code,
            'status'            => $this->status,
            'purchase_count'    => $this->purchase_count,
            'rewards_claimed'   => $this->rewards_claimed,
            'notes'             => $this->notes,
            'created_at'        => AppLibrary::datetime($this->created_at),
            'updated_at'        => AppLibrary::datetime($this->updated_at),
            'campaign'          => new CampaignResource($this->whenLoaded('campaign')),
        ];
    }
}
