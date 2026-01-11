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
        // Aggregated by item + price (per-order-line unit price)
        $unitPrice = $this->unit_price ?? $this->price ?? 0;

        return [
            "id"               => $this->item_id ?? $this->id,
            "name"             => $this->item_name ?? $this->name,
            "item_type"        => $this->item_type,
            "category_name"    => $this->category_name ?? ($this->category?->name ?? ''),
            "options_key"      => $this->options_key ?? null,
            "item_variations"  => isset($this->item_variations) ? json_decode($this->item_variations) : null,
            "item_extras"      => isset($this->item_extras) ? json_decode($this->item_extras) : null,
            "price"            => (float) $unitPrice,
            "flat_price"       => AppLibrary::flatAmountFormat($unitPrice),
            "convert_price"    => AppLibrary::convertAmountFormat($unitPrice),
            "currency_price"   => AppLibrary::currencyAmountFormat($unitPrice),
            "order"            => (int) ($this->total_quantity ?? $this->orders_count ?? 0),
            "total_income"     => (float) ($this->total_income ?? 0),
            "flat_total_income" => AppLibrary::flatAmountFormat($this->total_income ?? 0),
            "convert_total_income" => AppLibrary::convertAmountFormat($this->total_income ?? 0),
            "currency_total_income" => AppLibrary::currencyAmountFormat($this->total_income ?? 0),
            "created_at"       => $this->first_order_date ? date('Y-m-d', strtotime($this->first_order_date)) : '',
        ];
    }
}
