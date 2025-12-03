<?php
namespace App\Http\Resources\Website;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class typeDitaliServesPageFormResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'id'               => $this->id,
            'serves'           => [
                'serves_id'   => $this->type->id,
                'name'        => $this->type->name,
                'description' => $this->type->typeDitaliServices->short_description,
            ], 'form' => $this->form ? [
                'id'                    => $this->form->id,
                'header'                => $this->form->header,
                'sub_title'             => $this->form->sub_title,
                'step1_title'           => $this->form->step1_title,
                'step2_title'           => $this->form->step2_title,
                'service'               => $this->form->service,
                'name_last'             => $this->form->name_last,
                'name_first'            => $this->form->name_first,
                'email'                 => $this->form->email,
                'phone_number'          => $this->form->phone_number,
                'current_location'      => $this->form->current_location,
                'current_city'          => $this->form->current_city,
                'current_rooms_number'  => $this->form->current_rooms_number,
                'current_floor'         => $this->form->current_floor,
                'current_elevator'      => $this->form->current_elevator,
                'current_zipcode'       => $this->form->current_zipcode,
                'current_country'       => $this->form->current_country,
                'new_location'          => $this->form->new_location,
                'new_city'              => $this->form->new_city,
                'new_rooms_number'      => $this->form->new_rooms_number,
                'new_floor'             => $this->form->new_floor,
                'new_elevator'          => $this->form->new_elevator,
                'new_zipcode'           => $this->form->new_zipcode,
                'new_country'           => $this->form->new_country,
                'date'                  => $this->form->date,
                'offers_number'         => $this->form->offers_number,
                'other_details'         => $this->form->other_details,
                'note'                  => $this->form->note,
                'next_button'           => $this->form->next_button,
                'submit_button'         => $this->form->submit_button,
                'success_message'       => $this->form->success_message,
                'success_message_title' => $this->form->success_message_title,
                'image'                 => $this->form->image ? asset($this->form->image) : null,
            ] : null,
            'image'            => $this->image ? asset($this->image) : null,
            'title'            => $this->title,
            'description'      => $this->description,
            'meta_key'         => $this->meta_key,
            'body'             => $this->body,
            'meta_title'       => $this->meta_title,
            'meta_description' => $this->meta_description,
        ];
    }

}
