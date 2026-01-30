<?php

namespace App\Http\Resources;

use App\Libraries\AppLibrary;
use App\Enums\CampaignType;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\SimpleItemResource;
use App\Http\Resources\CampaignRegistrationResource;

class CampaignResource extends JsonResource
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
            'id'                   => $this->id,
            'name'                 => $this->name,
            'slug'                 => $this->slug,
            'description'          => $this->description,
            'type'                 => $this->type,
            'type_name'            => $this->type == CampaignType::PERCENTAGE ? 'Percentage' : 'Item',
            'discount_value'       => $this->discount_value === null ? 0 : $this->discount_value,
            'flat_discount_value'  => AppLibrary::flatAmountFormat($this->discount_value),
            'free_item_id'         => $this->free_item_id,
            'free_item'            => $this->whenLoaded('freeItem', function () {
                return new SimpleItemResource($this->freeItem);
            }),
            'required_purchases'   => $this->required_purchases,
            'status'               => $this->status,
            'convert_start_date'   => $this->start_date ? AppLibrary::datetime($this->start_date) : null,
            'convert_end_date'     => $this->end_date ? AppLibrary::datetime($this->end_date) : null,
            'start_date'           => $this->start_date,
            'end_date'             => $this->end_date,
            'registrations_count'  => $this->whenCounted('registrations'),
            'registrations'        => CampaignRegistrationResource::collection($this->whenLoaded('registrations')),
        ];
    }
}
