<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShoppingListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'offer' => $this->offer,
            'company' => new CompanyResource(User::find($this->user_id)), // استخدام العنصر مباشرة
            'type' => $this->type,
        ];

    }
}
