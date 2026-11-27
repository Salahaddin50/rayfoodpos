<?php

namespace App\Http\Resources;


use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'id'                             => $this->id,
            'order_serial_no'                => $this->order_serial_no,
            'token'                          => $this->token,
            'user_id'                        => $this->user_id,
            'branch_id'                      => $this->branch_id,
            'driver_id'                      => $this->driver_id,
            'driver_name'                    => $this->driver?->name,
            'branch_name'                    => optional($this->branch)->name,
            // Prefer withCount() from list queries; fall back to relation count for show/detail
            'order_items'                    => $this->order_items_count
                ?? optional($this->orderItems)->count()
                ?? 0,
            "total_currency_price"           => AppLibrary::currencyAmountFormat($this->total),
            "total_tax_currency_price"       => AppLibrary::currencyAmountFormat($this->total_tax),
            "total_amount_price"             => AppLibrary::flatAmountFormat($this->total),
            "discount_currency_price"        => AppLibrary::currencyAmountFormat($this->discount),
            "delivery_charge_currency_price" => AppLibrary::currencyAmountFormat($this->delivery_charge),
            "delivery_charge"                => $this->delivery_charge,
            "subtotal"                       => $this->subtotal,
            'payment_method'                 => $this->payment_method,
            'payment_status'                 => $this->payment_status,
            'preparation_time'               => $this->preparation_time,
            'order_type'                     => $this->order_type,
            'order_datetime'                 => AppLibrary::datetime($this->order_datetime),
            'status'                         => $this->status,
            'is_advance_order'               => $this->is_advance_order,
            'status_name'                    => trans('orderStatus.' . $this->status),
            'dining_table_id'                => $this->dining_table_id,
            'table_name'                     => $this->diningTable?->name,
            'whatsapp_number'                => $this->whatsapp_number,
            'location_url'                   => $this->location_url,
            'distance'                       => $this->distance,
            'pickup_option'                  => $this->pickup_option,
            'delivery_charge'                => $this->delivery_charge,
            // Relations should already be eager-loaded by the service; avoid extra load() N+1
            'customer'                       => $this->user ? new OrderUserResource($this->user) : null,
            'transaction'                    => $this->transaction ? new TransactionResource($this->transaction) : null,
        ];
    }
}
