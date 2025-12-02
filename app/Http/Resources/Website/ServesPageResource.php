<?php
namespace App\Http\Resources\Website;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServesPageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'name'                  => $this->name,
            'typeDitaliServices'    => $this->relationLoaded('typeDitaliServices')
            ? new TypeDitaliServicesResource($this->typeDitaliServices)
            : null,
            'type_serves_page_slug' => $this->relationLoaded('typeDitaliServesPageForm') && $this->typeDitaliServesPageForm
            ? $this->typeDitaliServesPageForm->slug
            : null,
            'typeTips'              => TypeTipsResource::collection($this->whenLoaded('TypeTips')),
            'typeFeature'           => TypeFeatureResource::collection($this->whenLoaded('TypeFeature')),
        ];

    }
}
