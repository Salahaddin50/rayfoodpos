<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'branch_id'      => $this->branch_id,
            'name'           => $this->name,
            'transport_type' => $this->transport_type,
            'whatsapp'       => $this->whatsapp,
            'status'         => $this->status,
        ];
    }
}


