<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminOfferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $lang = substr($request->header('Accept-Language', $request->get('lang', 'en')), 0, 2);

        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'email'            => $this->email,
            'phone'            => $this->phone,
            'date'             => $this->date,
            'execution_date'   => $this->execution_date,
            'count'            => $this->count,
            'Number_of_offers' => $this->Number_of_offers,
            'status'           => $this->status,
            'confirm_status'   => $this->confirm_status,
            'confirmed_at'     => $this->confirmed_at,
            'is_confirmed'     => $this->confirm_status === 'confirmed',
            'type'             => $this->when($this->relationLoaded('type') && $this->type, function () use ($lang) {
                return [
                    'id'   => $this->type->id,
                    'name' => $this->type->getTranslation('name', $lang),
                ];
            }),
            'country_id'       => $this->country_id,
            'city_id'          => $this->city_id,
            'latitude'         => $this->latitude,
            'longitude'        => $this->longitude,
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
        ];
    }
}
