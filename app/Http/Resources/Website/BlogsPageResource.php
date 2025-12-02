<?php
namespace App\Http\Resources\Website;

use App\Helpers\HelperFunc;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogsPageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'lastBlogPosts'    => $this->lastBlogPosts->map(function ($blog) {
                return [
                    'id'                => $blog->id,
                    'slug'              => $blog->slug,
                    'type_slug'         => $blog->type->typeDitaliServices->slug,
                    'image'             => HelperFunc::getLocalizedImage($blog->image) ? asset(HelperFunc::getLocalizedImage($blog->image)) : null, // Return multilingual small images                        'short_description' => $blog->short_description,
                    'short_description' => $blog->short_description,
                    'title'             => $blog->title,
                ];
            }),
            'allServesesBlogs' => $this->allServesesBlogs->map(function ($type) {
                return [
                    'id'    => $type->id,
                    'name'  => $type->name,
                    'slug'  => $type->typeDitaliServices?->slug,
                    'blogs' => $type->blogs->map(function ($blog) {
                        return [
                            'id'                => $blog->id,
                            'slug'              => $blog->slug,
                            'image'             => HelperFunc::getLocalizedImage($blog->image) ? asset(HelperFunc::getLocalizedImage($blog->image)) : null, // Return multilingual small images                        'short_description' => $blog->short_description,
                            'short_description' => $blog->short_description,
                            'title'             => $blog->title,
                            'created_at'        => $blog->created_at,
                        ];
                    }),
                ];
            }),
            'pageData'         => [
                'image'            => asset($this->page->image),
                'title'            => $this->page->title,
                'description'      => $this->page->description,
                'sub_title'        => $this->page->sub_title,
                'blog_categories'  => $this->page->blog_categories,
                'meta_key'         => $this->page->meta_key,
                'meta_title'       => $this->page->meta_title,
                'meta_description' => $this->page->meta_description,
            ],
        ];
    }
}
