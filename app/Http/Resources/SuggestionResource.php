<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SuggestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return
        ['id' => $this->id,
        'title' => $this->title,
        'description' => $this->description,
        'user_id' => $this->user_id,
        'government_entity_id' => $this->government_entity_id,
       'government_entity'=> $this->governmentEntity?->name, 
        'city_id' => $this->city_id,
        'created_at' => $this->created_at,
    ];
    }
}
