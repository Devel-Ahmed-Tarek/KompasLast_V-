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
        // Get language from Accept-Language header or query param, default to 'en'
        $lang = $request->header('Accept-Language', $request->get('lang', 'en'));
        $lang = substr($lang, 0, 2); // Take only first 2 characters (e.g., "en-US" → "en")

        return [
            'id' => $this->id,
            'type_id' => $this->type_id,
            'country_id' => $this->country_id,
            'city_id' => $this->city_id,
            'type' => [
                'id'         => $this->type->id,
                'name'       => $this->type->getTranslation('name', $lang),
                // السعر الحالي للخدمة (للعرض فقط)
                'price'      => $this->type->price,
                // سعر البيع المثبّت على الأوفر (ما يتأثرش بتغيير سعر الخدمة)
                'unit_price' => $this->unit_price ?? ($this->type->price / max(1, $this->Number_of_offers)),
            ],
            'country' => $this->when($this->countryRelation, function () use ($lang) {
                return [
                    'id' => $this->countryRelation->id,
                    'name' => $this->countryRelation->getTranslation('name', $lang),
                ];
            }),
            'city' => $this->when($this->cityRelation, function () use ($lang) {
                return [
                    'id' => $this->cityRelation->id,
                    'name' => $this->cityRelation->getTranslation('name', $lang),
                ];
            }),
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
