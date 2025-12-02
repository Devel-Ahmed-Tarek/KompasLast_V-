<?php
namespace App\Http\Resources\Website;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerPageResourcee extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [

            'id'            => $this->id,
            'first_section' => [
                'title'     => $this->first_section_title,
                'sub_title' => $this->first_section_sub_title,
            ],
            'title'         => $this->title,
            'sub_title'     => $this->sub_title,
            'body'          => $this->body,
            'cards'         => [
                [
                    'image'       => asset($this->image_card1),
                    'title'       => $this->title_card1,
                    'description' => $this->description_card1,
                ],
                [
                    'image'       => asset($this->image_card2),
                    'title'       => $this->title_card2,
                    'description' => $this->description_card2,
                ],
                [
                    'image'       => asset($this->image_card3),
                    'title'       => $this->title_card3,
                    'description' => $this->description_card3,
                ],
            ],
            'join_section'  => [
                'title'     => $this->join_title,
                'sub_title' => $this->join_sud_title,
                'steps'     => [
                    [
                        'image'       => asset($this->join_step_image1),
                        'title'       => $this->join_step_title1,
                        'description' => $this->join_step_description1,
                    ],
                    [
                        'image'       => asset($this->join_step_image2),
                        'title'       => $this->join_step_title2,
                        'description' => $this->join_step_description2,
                    ],
                    [
                        'image'       => asset($this->join_step_image3),
                        'title'       => $this->join_step_title3,
                        'description' => $this->join_step_description3,
                    ],
                ],
            ],
            'last_section'  => [
                'title'       => $this->last_section_title,
                'description' => $this->last_section_description,
                'button'      => $this->last_section_btn,
                'login'       => [
                    'title'     => $this->last_section_login_title,
                    'sub_title' => $this->last_section_login_sub_title,
                ],
            ],
            'meta'          => [
                'key'         => $this->meta_key,
                'meta_title'  => $this->meta_title,
                'description' => $this->meta_description,
            ],

        ];
    }
}
