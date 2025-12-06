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
        $locale = app()->getLocale(); // Get the current locale
        
        // جلب الصور الديناميكية
        $dynamicMedia = $this->getDynamicMedia($locale);
        
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
            'media'             => $dynamicMedia, // الصور الديناميكية (null إذا لم تكن موجودة)
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

    /**
     * جلب الصور الديناميكية للغة المحددة
     * ترجع null إذا لم تكن هناك صور
     */
    private function getDynamicMedia($locale)
    {
        if (!$this->relationLoaded('media')) {
            return null;
        }

        $media = $this->media()
            ->where('language', $locale)
            ->orderBy('field_name')
            ->orderBy('order')
            ->get();

        if ($media->isEmpty()) {
            return null; // إذا مفيش صور، نرجع null
        }

        $formatted = [];

        foreach ($media as $item) {
            $fieldName = $item->field_name;
            
            if (!isset($formatted[$fieldName])) {
                $formatted[$fieldName] = [];
            }

            $formatted[$fieldName][] = [
                'id' => $item->id,
                'file_path' => asset($item->file_path),
                'file_name' => $item->file_name,
                'file_type' => $item->file_type,
                'file_size' => $item->file_size,
                'order' => $item->order,
                'metadata' => $item->metadata,
            ];
        }

        // إذا كان في صورة واحدة فقط، نرجعها مباشرة (ليس array)
        foreach ($formatted as $fieldName => $images) {
            if (count($images) === 1) {
                $formatted[$fieldName] = $images[0];
            }
        }

        return $formatted;
    }
}
