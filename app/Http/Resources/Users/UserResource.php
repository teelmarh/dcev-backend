<?php

namespace App\Http\Resources\Users;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'email'            => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'first_name'       => $this->first_name,
            'last_name'        => $this->last_name,
            'middle_name'      => $this->middle_name,
            'phone'            => $this->phone,
            'gender'           => $this->gender,
            'nin_verified'     => $this->nin_verified,
            'nin_verified_at'  => $this->nin_verified_at,
            'created_at'       => $this->created_at,
        ];
    }
}
