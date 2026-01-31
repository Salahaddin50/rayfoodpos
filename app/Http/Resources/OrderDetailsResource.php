<?php

namespace App\Http\Resources;

use App\Enums\Ask;
use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailsResource extends JsonResource
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
            'id'                                  => $this->id,
            'order_serial_no'                     => $this->order_serial_no,
            'token'                               => $this->token,
            'driver_id'                           => $this->driver_id,
            'driver_name'                         => $this->driver?->name,
            'driver_whatsapp'                     => $this->driver?->whatsapp,
            "subtotal"                            => $this->subtotal,
            "discount"                            => $this->discount,
            "total_tax"                           => $this->total_tax,
            "total"                               => $this->total,
            "subtotal_currency_price"             => AppLibrary::currencyAmountFormat($this->subtotal),
            "subtotal_without_tax_currency_price" => AppLibrary::currencyAmountFormat($this->subtotal - $this->total_tax),
            "discount_currency_price"             => AppLibrary::currencyAmountFormat($this->discount),
            "delivery_charge"                     => $this->delivery_charge,
            "delivery_charge_currency_price"      => AppLibrary::currencyAmountFormat($this->delivery_charge),
            "pickup_cost"                         => $this->pickup_cost,
            "pickup_cost_currency_price"          => AppLibrary::currencyAmountFormat($this->pickup_cost),
            "total_currency_price"                => AppLibrary::currencyAmountFormat($this->total),
            "total_tax_currency_price"            => AppLibrary::currencyAmountFormat($this->total_tax),
            'order_type'                          => $this->order_type,
            'order_datetime'                      => AppLibrary::datetime($this->order_datetime),
            'order_date'                          => AppLibrary::date($this->order_datetime),
            'order_time'                          => AppLibrary::time($this->order_datetime),
            'delivery_date'                       => $this->is_advance_order == Ask::YES ? AppLibrary::increaseDate($this->order_datetime, 1) : AppLibrary::date($this->order_datetime),
            'delivery_time'                       => AppLibrary::deliveryTime($this->delivery_time),
            'payment_method'                      => $this->payment_method,
            'payment_status'                      => $this->payment_status,
            'is_advance_order'                    => $this->is_advance_order,
            'preparation_time'                    => $this->preparation_time,
            'status'                              => $this->status,
            'status_name'                         => trans('orderStatus.' . $this->status),
            'source'                              => $this->source,
            'reason'                              => $this->reason,
            'user'                                => new OrderUserResource($this->user?->load('roles', 'media')),
            'order_address'                       => new AddressResource($this->address),
            'branch'                              => new BranchResource($this->branch),
            'transaction'                         => new TransactionResource($this->transaction),
            'order_items'                         => OrderItemResource::collection($this->orderItems->load('orderItem')),
            'table_name'                          => $this->diningTable?->name,
            'dining_table_id'                     => $this->dining_table_id,
            'dining_table'                        => $this->diningTable,
            'whatsapp_number'                     => $this->whatsapp_number,
            'location_url'                        => $this->location_url,
            'campaign_id'                         => $this->campaign_id,
            'campaign_discount'                   => $this->campaign_discount,
            'campaign_discount_currency_price'    => AppLibrary::currencyAmountFormat($this->campaign_discount ?? 0),
            'campaign_redeem_free_item_id'        => $this->campaign_redeem_free_item_id,
            'campaign_snapshot'                   => $this->campaign_snapshot,
            'pickup_option'                       => $this->pickup_option,
            'takeaway_type_id'                    => $this->takeaway_type_id,
            'takeaway_type'                       => $this->takeawayType,
            'pos_payment_method'                  => $this->pos_payment_method,
            'pos_payment_note'                    => $this->pos_payment_note,
            'pos_note'                            => $this->pos_note,
            'pos_received_currency_amount'        => AppLibrary::currencyAmountFormat($this->pos_received_amount),
            'cash_back_amount'                    => $this->pos_received_amount - $this->total,
            'cash_back_currency_amount'           => AppLibrary::currencyAmountFormat($this->pos_received_amount - $this->total),
        ];
    }
}