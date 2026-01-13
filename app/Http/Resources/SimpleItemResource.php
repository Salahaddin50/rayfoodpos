<?php

namespace App\Http\Resources;


use App\Enums\Status;
use App\Libraries\AppLibrary;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class SimpleItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        $price = $this->price;
        $branchOverrideStatus = null;
        if ($this->relationLoaded('branchItemStatuses') && $this->branchItemStatuses && $this->branchItemStatuses->count() > 0) {
            $branchOverrideStatus = $this->branchItemStatuses->first()->status;
        }

        // If a branch-specific status exists, use it; otherwise fall back to the item status.
        $effectiveStatus = $branchOverrideStatus !== null ? $branchOverrideStatus : $this->status;
        return [
            "id"               => $this->id,
            "name"             => $this->name,
            "slug"             => $this->slug,
            "item_category_id" => $this->item_category_id,
            "tax_id"           => $this->tax_id,
            "flat_price"       => AppLibrary::flatAmountFormat($this->price),
            "convert_price"    => AppLibrary::convertAmountFormat($this->price),
            "currency_price"   => AppLibrary::currencyAmountFormat($this->price),
            "price"            => $this->price,
            "item_type"        => $this->item_type,
            "is_featured"      => $this->is_featured,
            "status"           => $this->status,
            "branch_status"    => $branchOverrideStatus,
            "effective_status" => $effectiveStatus,
            "description"      => $this->description === null ? '' : $this->description,
            "caution"          => $this->caution === null ? '' : $this->caution,
            "order"            => $this->orders_count ?? 0,
            "thumb"            => $this->thumb,
            "cover"            => $this->cover,
            "preview"          => $this->preview,
            "category_name"    => optional($this->category)->name,
            "category"         => $this->when($this->category, function() {
                return new ItemCategoryResource($this->category);
            }),
            "tax"              => $this->when($this->tax, function() {
                return new TaxResource($this->tax);
            }),
            "variations"       => $this->whenLoaded('variations', function() {
                return $this->variations->groupBy('item_attribute_id');
            }),
            "itemAttributes"   => $this->whenLoaded('variations', function() {
                return ItemAttributeResource::collection($this->itemAttributeList($this->variations));
            }),
            "extras"           => $this->whenLoaded('extras', function() {
                return ItemExtraResource::collection($this->extras);
            }),
            "addons"           => $this->whenLoaded('addons', function() {
                return ItemAddonResource::collection($this->addons);
            }),
            "offer"            => $this->whenLoaded('offer', function() use ($price) {
                return SimpleOfferResource::collection(
                    $this->offer->filter(function ($offer) use ($price) {
                        if (AppLibrary::isBetweenDate($offer->start_date, $offer->end_date) && $offer->status === Status::ACTIVE) {
                            $amount                = ($price - ($price / 100 * $offer->amount));
                            $offer->flat_price     = AppLibrary::flatAmountFormat($amount);
                            $offer->convert_price  = AppLibrary::convertAmountFormat($amount);
                            $offer->currency_price = AppLibrary::currencyAmountFormat($amount);
                            return $offer;
                        }
                    })
                );
            })
        ];
    }

    private function itemAttributeList($variations)
    {
        $array = [];
        foreach ($variations as $b) {
            if ($b->itemAttribute && !isset($array[$b->itemAttribute->id])) {
                $array[$b->itemAttribute->id] = (object)[
                    'id'     => $b->itemAttribute->id,
                    'name'   => $b->itemAttribute->name,
                    'status' => $b->itemAttribute->status
                ];
            }
        }
        return collect($array);
    }
}
