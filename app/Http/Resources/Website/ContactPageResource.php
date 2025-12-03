<?php
namespace App\Http\Resources\Website;

use App\Models\ConfigApp;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactPageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = ConfigApp::first();
        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'sub_title'        => $this->sub_title,
            'description'      => $this->description,
            'image'            => asset($this->image), // Adds the asset URL
            'form_title'       => $this->form_title,
            'form_sub_title'   => $this->form_sub_title,
            'information'      => $this->information,
            'meta_key'         => $this->meta_key,
            'meta_title'       => $this->meta_title,
            'meta_description' => $this->meta_description,
            'email'            => $data->email,
            'address'          => $data->address,
            'phone'            => $data->phone,
        ];
    }
}
