<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferWithAnswersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lang = $request->get('lang', 'en');

        return [
            'id' => $this->id,
            'type_id' => $this->type_id,
            'country_id' => $this->country_id,
            'city_id' => $this->city_id,
            'type' => [
                'id' => $this->type->id,
                'name' => $this->type->getTranslation('name', $lang),
                'price' => $this->type->price,
            ],
            'country' => $this->country,
            'city' => $this->city,
            'country_name' => $this->countryRelation->name,
            'city_name' => $this->cityRelation->name,
            'completion_status' => $this->completion_status,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'date' => $this->date,
            'answers' => OfferAnswerResource::collection($this->whenLoaded('answers')),
            'progress' => [
                'answered' => $this->answers->count(),
                'total' => $this->type->mainQuestions()->count(),
                'percentage' => $this->type->mainQuestions()->count() > 0
                    ? round(($this->answers->count() / $this->type->mainQuestions()->count()) * 100, 2)
                    : 0,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
