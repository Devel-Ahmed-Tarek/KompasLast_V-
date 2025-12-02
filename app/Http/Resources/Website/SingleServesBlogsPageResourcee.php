<?php
namespace App\Http\Resources\Website;

use App\Helpers\HelperFunc;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SingleServesBlogsPageResourcee extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'name'                  => $this->name,
            'slug'                  => $this->typeDitaliServices?->slug,
            'blog_meta_keys'        => $this->typeDitaliServices?->blog_meta_keys,
            'blog_meta_title'       => $this->typeDitaliServices?->blog_meta_title,
            'blog_meta_description' => $this->typeDitaliServices?->blog_meta_description,
            'blogs'                 => $this->blogs->map(function ($blog) {
                return [
                    'id'                => $blog->id,
                    'slug'              => $blog->slug,
                    'image'             => HelperFunc::getLocalizedImage($blog->image) ? asset(HelperFunc::getLocalizedImage($blog->image)) : null, // Return multilingual small images
                    'short_description' => $blog->short_description,
                    'title'             => $blog->title,
                    'created_at'        => $blog->created_at,
                ];
            }),

        ];
    }
}
