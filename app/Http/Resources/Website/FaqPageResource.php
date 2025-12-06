<?php
namespace App\Http\Resources\Website;

use App\Models\Faq;
use App\Models\FaqPage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FaqPageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Retrieve the first FAQ page
        $faqPage = FaqPage::with('media')->first();
        $locale  = app()->getLocale(); // Get the current locale
        $faqs    = Faq::select('question', 'answer')->get();
        
        // جلب الصور الديناميكية
        $dynamicMedia = $this->getDynamicMedia($faqPage, $locale);
        
        // معالجة hero_image (للتوافق مع النظام القديم)
        $heroImage = null;
        if ($faqPage) {
            $heroImages = json_decode($faqPage->hero_image, true) ?? [];
            $heroImage = isset($heroImages[$locale]) && $heroImages[$locale] ? asset($heroImages[$locale]) : null;
        }
        
        return [
            'page_data' => $faqPage ? [
                'id'               => $faqPage->id,
                'hero_image'       => $heroImage, // Generate asset URL for hero_image
                'title'            => $faqPage->title,                                         // Leave as-is
                'sub_title'        => $faqPage->sub_title,                                     // Leave as-is
                'form_title'       => $faqPage->form_title,                                    // Leave as-is
                'form_sub_title'   => $faqPage->form_sub_title,                                // Leave as-is
                'meta_key'         => $faqPage->meta_key,                                      // Leave as-is
                'meta_title'       => $faqPage->meta_title,                                    // Leave as-is
                'meta_description' => $faqPage->meta_description,                              // Leave as-is
                'media'            => $dynamicMedia, // الصور الديناميكية (null إذا لم تكن موجودة)

            ] : null,
            'faqs'      => $faqs->map(function ($faq) {
                return [
                    'question' => $faq->question,
                    'answer'   => $faq->answer,
                ];
            }),

        ];
    }

    /**
     * جلب الصور الديناميكية للغة المحددة
     * ترجع null إذا لم تكن هناك صور
     */
    private function getDynamicMedia($faqPage, $locale)
    {
        if (!$faqPage || !$faqPage->relationLoaded('media')) {
            return null;
        }

        $media = $faqPage->media()
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
