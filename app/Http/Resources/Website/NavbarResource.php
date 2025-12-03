<?php
namespace App\Http\Resources\Website;

use App\Models\ConfigApp;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NavbarResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $config = ConfigApp::first();

        return [
            'button'       => $this->button,
            'home'         => $this->home,
            'services'     => $this->services,
            'aboutUs'      => $this->aboutUs,
            'contactUs'    => $this->contactUs,
            'blogs'        => $this->blogs,
            'faqs'         => $this->faqs,
            'logo'         => $config?->logo ? asset($config->logo) : null,
            'logo_dark'    => $config?->logo_dark ? asset($config->logo_dark) : null,

            'servicesItem' => Type::with('typeDitaliServices:id,type_id,slug')->get()->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => $item->name,
                    'slug' => $item->typeDitaliServices?->slug,
                ];
            }),
        ];
    }
}
