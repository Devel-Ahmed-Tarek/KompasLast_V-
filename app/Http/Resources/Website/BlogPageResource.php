<?php
namespace App\Http\Resources\Website;

use App\Helpers\HelperFunc;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogPageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'type_id'           => $this->type_id,
            'title'             => $this->title,                                                                                                      // Return multilingual title
            'main_image'        => HelperFunc::getLocalizedImage($this->main_image) ? asset(HelperFunc::getLocalizedImage($this->main_image)) : null, // Return multilingual main images
            'image'             => HelperFunc::getLocalizedImage($this->image) ? asset(HelperFunc::getLocalizedImage($this->image)) : null,           // Return multilingual small images
            'short_description' => $this->short_description,                                                                                          // Return multilingual short descriptions
            'body'              => $this->body,                                                                                                       // Return multilingual body content
            'key_key'           => $this->key_key,                                                                                                    // Return multilingual key keys
            'meta_title'        => $this->meta_title,                                                                                                 // Return multilingual key keys
            'key_description'   => $this->key_description,                                                                                            // Return multilingual key descriptions
            'btn'               => $this->btn,                                                                                                        // Return multilingual button texts
            'btn_hrf'           => $this->btn_hrf,                                                                                                    // URL for button link
            'other_blogs'       => Blog::where('status', 1)
                ->where('id', '!=', $this->id)
                ->select('id', 'title', 'image', 'short_description', 'slug', 'type_id')
                ->with('type.typeDitaliServices')
                ->orderBy('id', 'desc')
                ->take(3)
                ->get()
                ->map(function ($blog) {
                    return [
                        'id'        => $blog->id,
                        'title'     => $blog->title,
                        'slug'      => $blog->slug,
                        'type_slug' => $blog->type->typeDitaliServices->slug,
                        'image'     => HelperFunc::getLocalizedImage($blog->image) ? asset(HelperFunc::getLocalizedImage($blog->image)) : null, // Return multilingual small images                        'short_description' => $blog->short_description,
                    ];
                }),

        ];
    }
}
