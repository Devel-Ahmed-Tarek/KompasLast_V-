<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrederCompanyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id"         => $this->id,
            "amount"     => $this->amount,
            "status"     => $this->status,
            "image"      => $this->image ? asset($this->image) : 'null',
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
    }
}
