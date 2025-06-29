<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CyberComplaintResource extends JsonResource
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
            'User_id' => $this->user_id,
            'type' => $this->type,
            'evidence_file' => $this->evidence_file,
             'evidence_file_url' => $this->evidence_file 
                ? asset('storage/' . $this->evidence_file) 
                : null,
            'Description' => $this->description,
            'related_link' => $this->related_link,
            'status' => $this->status,
            
        ];
    }
}
