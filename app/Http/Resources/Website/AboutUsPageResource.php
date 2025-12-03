<?php
namespace App\Http\Resources\Website;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AboutUsPageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id, // إذا كان لديك عمود ID
            'title'            => $this->title,
            'hero_image'       => asset($this->hero_image),
            'hero_title'       => $this->hero_title,
            'hero_description' => $this->hero_decsription,
            'why_title'        => $this->why_title,
            'why_sub_title'    => $this->why_sub_title,
            'why'              => [
                [
                    'name'        => $this->why_name,
                    'image'       => asset($this->why_imge),
                    'description' => $this->why_decsription,
                ],
                [
                    'name'        => $this->why_name2,
                    'image'       => asset($this->why_imge2),
                    'description' => $this->why_decsription2,
                ],
                [
                    'name'        => $this->why_name3,
                    'image'       => asset($this->why_imge3),
                    'description' => $this->why_decsription3,
                ],
            ],
            'target'           => [
                'title' => $this->target_title,
                'image' => asset($this->target_imge),
                'body'  => $this->target_body,
            ],
            'information'      => [
                'customer' => [
                    'name'        => $this->informaion_customer_name,
                    'description' => $this->informaion_customer_decsription,
                    'count'       => $this->informaion_customer_cont,
                ],
                'company'  => [
                    'name'        => $this->informaion_company_name,
                    'description' => $this->informaion_company_decsription,
                    'count'       => $this->informaion_company_cont,
                ],
                'offer'    => [
                    'name'        => $this->informaion_offer_name,
                    'description' => $this->informaion_offer_decsription,
                    'count'       => $this->informaion_offer_cont,
                ],
            ],
            'meta'             => [
                'key'         => $this->meta_key,
                'meta_title'  => $this->meta_title,
                'description' => $this->meta_description,
            ],
        ];
    }
}
