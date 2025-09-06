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
            'user_id' => $this->anonymous == 1 ? 'x' : $this->user_id,
            'government_entity_id' => $this->government_entity_id,
            'government_entity'=> $this->governmentEntity?->name, 
            'City_id' => $this->city_id,
            'attachments' => $this->attachments,
            'description' => $this->description,
            'is_emergency' => $this->is_emergency,
            'status' => $this->status,
            'anonymous'=>$this->anonymous,
            'map_iframe' => $this->map_iframe,
            'created_at'=>$this->created_at,
        ];
    }
}
