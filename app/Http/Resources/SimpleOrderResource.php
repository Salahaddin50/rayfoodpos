<?php

namespace App\Http\Resources;


use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;

class SimpleOrderResource extends JsonResource
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
            'id'                           => $this->id,
            'order_serial_no'              => $this->order_serial_no,
            'token'                        => $this->token,
            'driver_id'                    => $this->driver_id,
            'driver_name'                  => $this->driver?->name,
            'order_datetime'               => AppLibrary::datetime($this->order_datetime),
            "subtotal"                     => $this->subtotal,
            "subtotal_currency_price"       => AppLibrary::currencyAmountFormat($this->subtotal),
            "subtotal_amount_price"         => AppLibrary::flatAmountFormat($this->subtotal),
            "total_currency_price"         => AppLibrary::currencyAmountFormat($this->total),
            "total_amount_price"           => AppLibrary::flatAmountFormat($this->total),
            "discount_amount_price"        => AppLibrary::flatAmountFormat($this->discount),
            "discount_currency_price"      => AppLibrary::currencyAmountFormat($this->discount),
            "delivery_charge"              => $this->delivery_charge,
            "delivery_charge_amount_price" => AppLibrary::flatAmountFormat($this->delivery_charge),
            "delivery_charge_currency_price" => AppLibrary::currencyAmountFormat($this->delivery_charge),
            "pickup_cost"                  => $this->pickup_cost,
            "pickup_cost_amount_price"     => AppLibrary::flatAmountFormat($this->pickup_cost),
            'payment_method'               => $this->payment_method,
            'payment_status'               => $this->payment_status,
            'transaction'                  => $this->transaction ? strtoupper($this->transaction?->payment_method) : null,
            'order_type'                   => $this->order_type,
            'source'                       => $this->source,
            'pos_payment_method'           => $this->pos_payment_method,
            'status'                       => $this->status,
            'status_name'                  => trans('orderStatus.' . $this->status),
            'customer_name'                => $this->user?->name,
            'table_name'                   => $this->diningTable?->name,
            'whatsapp_number'              => $this->whatsapp_number,
            'location_url'                 => $this->location_url,
            'takeaway_type'                => $this->takeawayType,
            'takeaway_type_name'           => $this->takeawayType?->name,
            'pos_note'                     => $this->pos_note,
        ];
    }
}