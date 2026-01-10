<?php

namespace App\Http\Resources;


use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        // This resource now receives a stdClass object from the raw query
        // instead of an Item model
        
        return [
            "id"               => $this->item_id,
            "name"             => $this->item_name,
            "item_type"        => $this->item_type,
            "category_name"    => $this->category_name ?? '',
            "options_key"      => $this->options_key ?? null,
            "item_variations"  => isset($this->item_variations) ? json_decode($this->item_variations) : null,
            "item_extras"      => isset($this->item_extras) ? json_decode($this->item_extras) : null,
            "price"            => (float) $this->unit_price,
            "flat_price"       => AppLibrary::flatAmountFormat($this->unit_price),
            "convert_price"    => AppLibrary::convertAmountFormat($this->unit_price),
            "currency_price"   => AppLibrary::currencyAmountFormat($this->unit_price),
            "order"            => (int) $this->total_quantity,
            "total_income"     => (float) $this->total_income,
            "flat_total_income" => AppLibrary::flatAmountFormat($this->total_income),
            "convert_total_income" => AppLibrary::convertAmountFormat($this->total_income),
            "currency_total_income" => AppLibrary::currencyAmountFormat($this->total_income),
            "created_at"       => $this->first_order_date ? date('Y-m-d', strtotime($this->first_order_date)) : '',
        ];
    }
}
