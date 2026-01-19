<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OSSOrderDetailsResource extends JsonResource
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
            // Dining-table (and other) orders may not have tokens. Return null if no token, so frontend can check properly.
            'token'                               => $this->token,
            'order_type'                          => $this->order_type,
            'status'                              => $this->status,
            'dining_table_id'                     => $this->dining_table_id,
            'dining_table'                        => $this->diningTable,
            'table_name'                          => $this->diningTable?->name,
            'whatsapp_number'                     => $this->whatsapp_number,
            'takeaway_type_id'                    => $this->takeaway_type_id,
            'takeaway_type'                       => $this->takeawayType,
            'takeaway_type_name'                  => $this->takeawayType?->name,
        ];
    }
}
