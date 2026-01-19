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
                $countryName = $this->country->getTranslation('name', $lang, false);
                if (!$countryName) {
                    $nameArray = is_array($this->country->name) ? $this->country->name : json_decode($this->country->name, true);
                    $countryName = $nameArray[$lang] ?? $nameArray['en'] ?? '';
                }
                return [
                    'id' => $this->country->id,
                    'name' => $countryName,
                ];
            }),
            'city' => $this->when($this->city, function () use ($lang) {
                $cityName = $this->city->getTranslation('name', $lang, false);
                if (!$cityName) {
                    $nameArray = is_array($this->city->name) ? $this->city->name : json_decode($this->city->name, true);
                    $cityName = $nameArray[$lang] ?? $nameArray['en'] ?? '';
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
