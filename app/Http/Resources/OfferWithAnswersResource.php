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
            'country' => $this->when($this->country, function () use ($lang) {
                // Get name - handle both array and JSON string formats
                $nameValue = $this->country->name;

                if (is_array($nameValue)) {
                    $countryName = $nameValue[$lang] ?? $nameValue['en'] ?? '';
                } elseif (is_string($nameValue)) {
                    $nameArray = json_decode($nameValue, true);
                    if (is_array($nameArray)) {
                        $countryName = $nameArray[$lang] ?? $nameArray['en'] ?? '';
                    } else {
                        $countryName = '';
                    }
                } else {
                    $countryName = '';
                }

                return [
                    'id' => $this->country->id,
                    'name' => $countryName,
                ];
            }),
            'city' => $this->when($this->city, function () use ($lang) {
                // Get name - handle both array and JSON string formats
                $nameValue = $this->city->name;

                if (is_array($nameValue)) {
                    $cityName = $nameValue[$lang] ?? $nameValue['en'] ?? '';
                } elseif (is_string($nameValue)) {
                    $nameArray = json_decode($nameValue, true);
                    if (is_array($nameArray)) {
                        $cityName = $nameArray[$lang] ?? $nameArray['en'] ?? '';
                    } else {
                        $cityName = '';
                    }
                } else {
                    $cityName = '';
                }

                return [
                    'id' => $this->city->id,
                    'name' => $cityName,
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
