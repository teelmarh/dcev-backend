<?php

namespace App\Http\Resources\Users;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use App\Models\Permission;

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
            'date_of_birth'    => $this->date_of_birth,
            'phone'            => $this->phone,
            'gender'           => $this->gender,
            'photo'            => $this->photo ? Storage::disk('public')->url($this->photo) : null,
            'nin'                => $this->nin,
            'nin_verified'       => $this->nin_verified,
            'nin_verified_at'    => $this->nin_verified_at,
            'empic_synced'       => $this->empic_synced,
            'empic_status'       => $this->empic_status,
            'empic_customer_no'  => $this->empic_customer_no,
            'empic_address_id'   => $this->empic_address_id,
            'role'               => $this->role,
            'regional_office_id' => $this->regional_office_id,
            'permissions'        => $this->role === 'superadmin'
                ? Permission::orderBy('slug')->pluck('slug')
                : $this->resolvedPermissionSlugs()->values(),
            'created_at'         => $this->created_at,
        ];
    }
}
