<?php

namespace App\Http\Resources;


use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemExtraResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request) : array
    {
        $price = $this->price ?? 0;
        return [
            'id'             => $this->id,
            'item_id'        => $this->item_id,
            'name'           => $this->name,
            'price'          => $price,
            'currency_price' => AppLibrary::currencyAmountFormat($price),
            'flat_price'     => AppLibrary::flatAmountFormat($price),
            'convert_price'  => AppLibrary::convertAmountFormat($price),
            'status'         => $this->status,
            "item"           => optional($this->item)->name,
        ];
    }
}
