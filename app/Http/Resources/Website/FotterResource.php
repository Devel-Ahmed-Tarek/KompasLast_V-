<?php
namespace App\Http\Resources\Website;

use App\Models\ConfigApp;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FotterResource extends JsonResource
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
            'loin_btn'     => $this->loin_btn,
            'description'  => $this->label_input,
            'pages'        => [
                'home'      => $this->home,
                'services'  => $this->services,
                'aboutUs'   => $this->aboutUs,
                'contactUs' => $this->contactUs,
                'blogs'     => $this->blogs,
                'imprint'   => $this->imprint,
                'compalins' => $this->compalins,
                'faqs'      => $this->faqs,
            ],
            'mida'         => [
                'facebook'  => $config?->facebook,
                'linkedin'  => $config?->linkedin,
                'instagram' => $config?->istagram, // Fixed spelling
                'tiktok'    => $config?->tiktok,
                'twitter'   => $config?->twiter, // Fixed spelling
                'threads'   => $config?->threads,
            ],
            'info'         => [
                'email'   => $config?->email,
                'address' => $config?->address,
                'phone'   => $config?->phone,
                'logo'    => $config?->logo ? asset($config->logo) : null,
            ],
            'servicesItem' => Type::whereNull('parent_id')
                ->where('is_active', true)
                ->with([
                    'typeDitaliServices:id,type_id,slug',
                    'children' => function ($query) {
                        $query->where('is_active', true)->orderBy('order');
                    },
                    'children.typeDitaliServices:id,type_id,slug'
                ])
                ->orderBy('order')
                ->get()
                ->map(function ($item) {
                    return [
                        'id'       => $item->id,
                        'name'     => $item->name,
                        'slug'     => $item->typeDitaliServices?->slug,
                        'children' => $item->children->map(function ($child) {
                            return [
                                'id'   => $child->id,
                                'name' => $child->name,
                                'slug' => $child->typeDitaliServices?->slug,
                            ];
                        }),
                    ];
                }),
        ];
    }
}
