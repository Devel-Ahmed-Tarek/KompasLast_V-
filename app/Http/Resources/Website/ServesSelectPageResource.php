<?php

namespace App\Http\Resources\Website;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServesSelectPageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'slug' => $this->typeDitaliServices?->slug,
            'parent_id' => $this->parent_id,
            'is_parent' => is_null($this->parent_id),
        ];

        // Include children if they are loaded and exist
        if ($this->relationLoaded('children') && $this->children->isNotEmpty()) {
            $data['children'] = ServesSelectPageResource::collection($this->children);
        }

        return $data;
    }
}
