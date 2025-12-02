<?php
namespace App\Http\Resources\Website;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TypeDitaliServicesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale(); // Get the current locale

        return [
            'main_image'        => isset($this->main_image[$locale]) ? asset($this->main_image[$locale]) : null,
            'small_image'       => isset($this->small_image[$locale]) ? asset($this->small_image[$locale]) : null,
            'short_description' => $this->short_description,
            'feature_header'    => $this->feature_header,
            'feature_sub_title' => $this->feature_sub_title,
            'body'              => $this->body,
            'slug'              => $this->slug,
            'tips_title'        => $this->tips_title,
            'tips_subtitle'     => $this->tips_subtitle,
            'meta_keys'         => $this->meta_keys,
            'meta_title'        => $this->meta_title,
            'meta_description'  => $this->meta_description,
        ];
    }
}
