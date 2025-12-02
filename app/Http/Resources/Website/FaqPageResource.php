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
        $faqPage = FaqPage::first();
        $locale  = app()->getLocale(); // Get the current locale
        $faqs    = Faq::select('question', 'answer')->get();
        return [
            'page_data' => $faqPage ? [
                'id'               => $faqPage->id,
                'hero_image'       => asset(json_decode($faqPage->hero_image, true)[$locale]), // Generate asset URL for hero_image
                'title'            => $faqPage->title,                                         // Leave as-is
                'sub_title'        => $faqPage->sub_title,                                     // Leave as-is
                'form_title'       => $faqPage->form_title,                                    // Leave as-is
                'form_sub_title'   => $faqPage->form_sub_title,                                // Leave as-is
                'meta_key'         => $faqPage->meta_key,                                      // Leave as-is
                'meta_title'       => $faqPage->meta_title,                                    // Leave as-is
                'meta_description' => $faqPage->meta_description,                              // Leave as-is

            ] : null,
            'faqs'      => $faqs->map(function ($faq) {
                return [
                    'question' => $faq->question,
                    'answer'   => $faq->answer,
                ];
            }),

        ];
    }
}
