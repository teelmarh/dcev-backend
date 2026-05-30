<?php

namespace App\Http\Resources\Users;

use Illuminate\Http\Request;

class NinVerifiedResource extends UserResource
{
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'nin_photo' => $this->nin_photo,
        ]);
    }
}
