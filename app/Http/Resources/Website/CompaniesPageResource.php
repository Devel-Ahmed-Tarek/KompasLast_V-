<?php

namespace App\Http\Resources\Website;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompaniesPageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'img' => $this->img ? asset($this->img) : 'https://ui-avatars.com/api/?name=' . urlencode($this->name),
            'about' => $this->companyDetails->about ?? null,
            'types_comapny' => $this->typesComapny->map(function ($type) {
                return [
                    'name' => $type->name,
                ];
            }),
        ];
    }
}
