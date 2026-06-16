<?php

namespace App\Http\Resources\Officer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BiometricCaptureResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'licence_id'       => $this->licence_id,
            'captured_by'      => $this->whenLoaded('capturedBy', fn () => [
                'id'         => $this->capturedBy->id,
                'first_name' => $this->capturedBy->first_name,
                'last_name'  => $this->capturedBy->last_name,
            ]),
            'photo_url'        => $this->photo_path
                ? Storage::disk('public')->url($this->photo_path)
                : null,
            'left_index_wsq'   => $this->left_index_wsq,
            'right_index_wsq'  => $this->right_index_wsq,
            'signature_url'    => $this->signature_path
                ? Storage::disk('public')->url($this->signature_path)
                : null,
            'is_complete'      => $this->isComplete(),
            'completed_at'     => $this->completed_at?->toISOString(),
            'created_at'       => $this->created_at->toISOString(),
            'updated_at'       => $this->updated_at->toISOString(),
        ];
    }
}
