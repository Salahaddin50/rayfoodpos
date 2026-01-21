<?php

namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
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
            "id"        => $this->id,
            "name"      => $this->name,
            "email"     => $this->email === null ? '' : $this->email,
            "phone"     => $this->phone === null ? '' : $this->phone,
            "latitude"  => $this->latitude === null ? '' : $this->latitude,
            "longitude" => $this->longitude === null ? '' : $this->longitude,
            "city"      => $this->city,
            "state"     => $this->state,
            "zip_code"  => $this->zip_code,
            "address"   => $this->address,
            "status"    => $this->status,

            // Branch-specific delivery rules (nullable)
            "free_delivery_threshold"       => $this->free_delivery_threshold,
            "free_delivery_distance"        => $this->free_delivery_distance,
            "delivery_distance_threshold_1" => $this->delivery_distance_threshold_1,
            "delivery_distance_threshold_2" => $this->delivery_distance_threshold_2,
            "delivery_cost_1"               => $this->delivery_cost_1,
            "delivery_cost_2"               => $this->delivery_cost_2,
            "delivery_cost_3"               => $this->delivery_cost_3,

        ];
    }
}
