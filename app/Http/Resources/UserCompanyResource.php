<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserCompanyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "email" => $this->email,
            "phone" => $this->phone,
            "count_offer" => $this->copany_details->count_offer,
            "total" => $this->copany_details->total,
            "logo" => $this->copany_details->loge1,
            "website" => $this->copany_details->website,
        ];
    }
}
