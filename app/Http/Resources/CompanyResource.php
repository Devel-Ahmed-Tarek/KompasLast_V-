<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
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
            "ban" => $this->ban,
            "phone" => $this->phone,
            "img" => asset($this->img),
            "compnyDital" => [
                "id" => $this->companyDetails->id,
                "user_id" => $this->companyDetails->user_id,
                "count_offer" => $this->companyDetails->count_offer,
                "total" => $this->companyDetails->total,
                "sucsses" => $this->companyDetails->sucsses,
                "loge1" => $this->companyDetails->loge1,
                "file" => $this->companyDetails->file ? asset($this->companyDetails->file) : null,
                "file2" => $this->companyDetails->file2 ? asset($this->companyDetails->file2) : null,
                "file3" => $this->companyDetails->file3 ? asset($this->companyDetails->file3) : null,
                "description" => $this->companyDetails->description,
                "reg_name" => $this->companyDetails->reg_name,
                "founded_year" => $this->companyDetails->founded_year,
                "owner_name" => $this->companyDetails->owner_name,
                "website" => $this->companyDetails->website,
                "address" => $this->companyDetails->address,
                "country" => $this->companyDetails->country,
                "phone2" => $this->companyDetails->phone2,
                "number" => $this->companyDetails->number,
                "counties" => $this->companyDetails->counties,
                "about" => $this->companyDetails->about,
                "receive_offers" => $this->companyDetails->receive_offers,
                "created_at" => $this->companyDetails->created_at,
                "banc_count" => $this->companyDetails->banc_count,
                "status" => $this->companyDetails->status,
                "banc_name" => $this->companyDetails->banc_name,
                "updated_at" => $this->companyDetails->updated_at,
                "banc_ip" => $this->companyDetails->banc_ip,
            ],
            "typesComapny" => $this->typesComapny,
            "review" => [
                'avg' => $this->review()->avg('stars'),
                'countReview' => $this->review()->count(),
            ],
            "orderList" => [
                "all" => $this->shopping_list()->count() ?? 0,
                "shop" => $this->shopping_list()->where('type', 'S')->count() ?? 0,
                "dinamic" => $this->shopping_list()->where('type', 'd')->count() ?? 0,
            ],
            "wallet" => $this->wallet,
        ];

    }
}
