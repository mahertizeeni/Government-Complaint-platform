<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplaintResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'government_entity_id' => $this->government_entity_id,
            'City_id' => $this->city_id,
            'attachments' => $this->attachments,
            'attachments_url' => $this->attachments 
                ? asset('storage/' . $this->attachments) 
                : null,
            'description' => $this->description,
            'is_emergency' => $this->is_emergency,
            'status' => $this->status,
            'anonymous'=>$this->anonymous,
            'map_iframe' => $this->map_iframe,
        ];
    }
}
