<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferShopResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type_id'          => [
                'id'    => $this->type_id,
                'name'  => $this->type->name,
                'price' => $this->Type->price / $this->Number_of_offers,
            ],
            'id'               => $this->id,
            'name'             => $this->name,
            'email'            => $this->email,
            'phone'            => $this->phone,
            'date'             => $this->date,
            'adresse'          => $this->adresse,
            'ort'              => $this->ort,
            'zimmer'           => $this->zimmer,
            'etage'            => $this->etage,
            'Number_of_offers' => $this->Number_of_offers,
            'vorhanden'        => $this->vorhanden,
            'Nach_Adresse'     => $this->Nach_Adresse,
            'Nach_Ort'         => $this->Nach_Ort,
            'Nach_Zimmer'      => $this->Nach_Zimmer,
            'Nach_Etage'       => $this->Nach_Etage,
            'Nach_vorhanden'   => $this->Nach_vorhanden,
            'count'            => $this->count,
            'Besonderheiten'   => $this->Besonderheiten,
            'ip'               => $this->ip,
            'country'          => $this->country,
            'city'             => $this->city,
            'lang'             => $this->lang,
            'cheek'            => $this->cheek,
            'status'           => $this->status,
        ];
    }
}
