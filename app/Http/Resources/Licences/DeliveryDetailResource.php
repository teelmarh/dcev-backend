<?php

namespace App\Http\Resources\Licences;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'licence_id'            => $this->licence_id,
            'recipient_name'        => $this->recipient_name,
            'recipient_phone'       => $this->recipient_phone,
            'address_line'          => $this->address_line,
            'city'                  => $this->city,
            'state'                 => $this->state,
            'lga'                   => $this->lga,
            'postal_code'           => $this->postal_code,
            'courier_instructions'  => $this->courier_instructions,
            'delivery_paid'         => $this->isPaid(),
        ];
    }
}
