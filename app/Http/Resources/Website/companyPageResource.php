<?php

namespace App\Http\Resources\Website;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class companyPageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale(); // Get the current locale

        return [
            "id" => $this->id,
            "name" => $this->name,
            "email" => $this->email,
            "phone" => $this->phone,
            "img" => asset($this->img),
            "compnyDital" => [
                "id" => $this->companyDetails->id,
                "founded_year" => $this->companyDetails->founded_year,
                "owner_name" => $this->companyDetails->owner_name,
                "about" => $this->companyDetails->about,
            ],
            "typesComapny" => $this->typesComapny->map(function ($type) {
                return [
                    'name' => $type->name,
                    'short_description' => $type->typeDitaliServices->short_description,
                ];
            }),
            "allReview" => [
                'reviews' => $this->review->map(function ($itme) {
                    return [
                        'name' => $itme->name,
                        'email' => $itme->email,
                        'comment' => $itme->comment,
                        'stars' => $itme->stars,
                    ];
                }),
                'countReview' => $this->review()->count(),
            ],
            "orderList" => [
                "all" => $this->shopping_list()->count() ?? 0,
            ],
        ];
    }
}
