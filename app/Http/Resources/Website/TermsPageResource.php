<?php
namespace App\Http\Resources\Website;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TermsPageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'title'            => $this->title,
            'body'             => $this->body,
            'meta_key'         => $this->meta_key,
            'meta_title'       => $this->meta_title,
            'meta_description' => $this->meta_description,
        ];
    }
}
