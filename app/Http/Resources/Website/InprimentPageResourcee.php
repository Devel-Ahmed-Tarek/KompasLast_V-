<?php
namespace App\Http\Resources\Website;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InprimentPageResourcee extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'title' => $this->title,
            'body'  => $this->body,
            'meta'  => [
                'key'         => $this->meta_key,
                'meta_title'  => $this->meta_title,
                'description' => $this->meta_description,
            ],

        ];
    }
}
