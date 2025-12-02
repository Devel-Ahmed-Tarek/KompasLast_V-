<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAdminResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return
            [
            'id'    => $request->id,
            'name'  => $request->name,
            'email' => $request->email,
            'img'   => $request->img,
            'ban'   => $request->ban,
            'role'  => $request->role,
        ];
    }
}
