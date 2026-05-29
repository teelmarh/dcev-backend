<?php

namespace App\Http\Resources\System;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EndpointResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'method' => $this['method'],
            'uri' => $this['uri'],
            'name' => $this['name'],
            'action' => $this['action'],
            'middleware' => $this['middleware'],
        ];
    }
}
