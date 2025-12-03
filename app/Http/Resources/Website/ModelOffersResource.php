<?php
namespace App\Http\Resources\Website;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ModelOffersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'title'       => $this->title,
            'description' => $this->description,
            'img'         => $this->img ? asset($this->img) : null,
            'link'        => $this->link,
            'btn'         => $this->btn,
        ];
    }
}
