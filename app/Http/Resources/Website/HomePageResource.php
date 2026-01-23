<?php

namespace App\Http\Resources\Website;

use App\Helpers\HelperFunc;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomePageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale(); // Get the current locale

        // جلب الصور الديناميكية
        $dynamicMedia = $this->getDynamicMedia($this->home, $locale);

        return [
            'home'               => [
                'hero_image'                    => $this->home->hero_imge,
                'hero_title'                    => $this->home->hero_title,
                'hero_description'              => $this->home->hero_description,
                'hero_sub_title'                => $this->home->hero_sub_title,
                'our_clients_pinions_sub_title' => $this->home->our_clients_pinions_sub_title,
                'hero_button'                   => $this->home->hero_button,
                'sub_our_trusted_Companies'     => $this->home->sub_our_trusted_Companies,
                'meta_description'              => $this->home->meta_description,
                'meta_key'                      => $this->home->meta_key,
                'meta_title'                    => $this->home->meta_titel,
                'parteners_Section'             => [
                    'parteners_title'       => $this->home->parteners_title,
                    'parteners_sub_title'   => $this->home->parteners_sub_title,
                    'parteners_description' => $this->home->parteners_description,
                    'parteners_btn'         => $this->home->parteners_btn,
                    'cards'                 => [
                        [
                            'icon'        => asset($this->partnerPage->image_card1),
                            'name'        => $this->partnerPage->title_card1,
                            'description' => $this->partnerPage->description_card1,
                        ],
                        [
                            'icon'        => asset($this->partnerPage->image_card2),
                            'name'        => $this->partnerPage->title_card2,
                            'description' => $this->partnerPage->description_card2,
                        ],
                        [
                            'icon'        => asset($this->partnerPage->image_card3),
                            'name'        => $this->partnerPage->title_card3,
                            'description' => $this->partnerPage->description_card3,
                        ],
                    ],
                ],
                'work_title'                    => $this->home->work_title,
                'work_sub_title'                => $this->home->work_sub_title,
                'work'                          => [
                    [
                        'name'        => $this->home->work_name,
                        'icon'        => $this->home->work_icon,
                        'description' => $this->home->work_description,
                    ],
                    [
                        'name'        => $this->home->work_name2,
                        'icon'        => $this->home->work_icon2,
                        'description' => $this->home->work_description2,
                    ],
                    [
                        'name'        => $this->home->work_name3,
                        'icon'        => $this->home->work_icon3,
                        'description' => $this->home->work_description3,
                    ],
                ],
                'review_form_title'             => $this->home->review_form_title,
                'review_form_sub_title'         => $this->home->review_form_sub_title,
                'our_clients_opinions'          => $this->home->our_clients_pinions,
                'faq_image'                     => $this->home->faq_image,
                'our_trusted_companies'         => $this->home->our_trusted_Companies,
                'what_clients_say_about'        => $this->home->what_clients_say_cabout,
                'media'                          => $dynamicMedia, // الصور الديناميكية (null إذا لم تكن موجودة)
            ],

            'services_title'     => $this->home->services_title,
            'services_sub_title' => $this->home->services_sub_title,
            'services'           => $this->services->map(function ($service) {
                return $this->formatService($service);
            }),

            'reviewCompany'      => $this->reviewCompany,
            'reviewSite'         => $this->reviewSite,

            'faq_title'          => $this->home->faq_title,
            'faq_sub_title'      => $this->home->faq_sub_title,
            'faq'                => $this->faq->map(function ($item) {
                return [
                    'question' => $item->question,
                    'answer'   => $item->answer,
                ];
            }),

            'form'               => $this->form ? [
                'id'                    => $this->form->id,
                'header'                => $this->form->header,
                'sub_title'             => $this->form->sub_title,
                'step1_title'           => $this->form->step1_title,
                'step2_title'           => $this->form->step2_title,
                'service'               => $this->form->service,
                'name_last'             => $this->form->name_last,
                'name_first'            => $this->form->name_first,
                'email'                 => $this->form->email,
                'phone_number'          => $this->form->phone_number,
                'current_location'      => $this->form->current_location,
                'current_city'          => $this->form->current_city,
                'current_rooms_number'  => $this->form->current_rooms_number,
                'current_floor'         => $this->form->current_floor,
                'current_elevator'      => $this->form->current_elevator,
                'current_zipcode'       => $this->form->current_zipcode,
                'current_country'       => $this->form->current_country,
                'new_location'          => $this->form->new_location,
                'new_city'              => $this->form->new_city,
                'new_rooms_number'      => $this->form->new_rooms_number,
                'new_floor'             => $this->form->new_floor,
                'new_elevator'          => $this->form->new_elevator,
                'new_zipcode'           => $this->form->new_zipcode,
                'new_country'           => $this->form->new_country,
                'date'                  => $this->form->date,
                'offers_number'         => $this->form->offers_number,
                'other_details'         => $this->form->other_details,
                'note'                  => $this->form->note,
                'next_button'           => $this->form->next_button,
                'submit_button'         => $this->form->submit_button,
                'success_message'       => $this->form->success_message,
                'success_message_title' => $this->form->success_message_title,
                'image'                 => $this->form->image ? asset($this->form->image) : null,
            ] : null,

            'companeis'          => $this->companeis->map(function ($company) {
                return [
                    'id'   => $company->id,
                    'name' => $company->name,
                    'logo' => $company->img ? asset($company->img) : null,
                ];
            }),
        ];
    }

    /**
     * جلب الصور الديناميكية للغة المحددة
     * ترجع null إذا لم تكن هناك صور
     */
    private function getDynamicMedia($home, $locale)
    {
        if (!$home->relationLoaded('media')) {
            return null;
        }

        $media = $home->media()
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

    /**
     * تنسيق الخدمة (تستخدم للـ Parent والـ Children)
     */
    private function formatService($service)
    {
        $typeDetails = optional($service->typeDitaliServices);
        $locale = app()->getLocale();

        // جلب الصور الديناميكية
        $dynamicMedia = $this->getServiceMedia($typeDetails, $locale);

        $formatted = [
            'id'                => $service->id,
            'name'              => $service->name,
            'price'             => $service->price ?? null,
            'short_description' => $typeDetails->short_description ?? 'No description available',
            'slug'              => $typeDetails->slug ?? 'No description available',
            'small_image'       => HelperFunc::getLocalizedImage($typeDetails->small_image) ? asset(HelperFunc::getLocalizedImage($typeDetails->small_image)) : null,
            'service_home_icon' => $typeDetails->service_home_icon ? asset($typeDetails->service_home_icon) : null,
            'media'             => $dynamicMedia,
            'is_parent'         => is_null($service->parent_id),
        ];

        // إضافة الأنواع الفرعية (Children) إذا كانت موجودة
        if ($service->relationLoaded('children') && $service->children->isNotEmpty()) {
            $formatted['children'] = $service->children->map(function ($child) {
                return $this->formatService($child);
            });
        } else {
            $formatted['children'] = [];
        }

        return $formatted;
    }

    /**
     * جلب الصور الديناميكية للخدمة
     */
    private function getServiceMedia($typeDetails, $locale)
    {
        $dynamicMedia = [];
        
        if ($typeDetails && method_exists($typeDetails, 'relationLoaded') && $typeDetails->relationLoaded('media')) {
            $media = $typeDetails->media()
                ->where('language', $locale)
                ->orderBy('field_name')
                ->orderBy('order')
                ->get();

            foreach ($media as $item) {
                $fieldName = $item->field_name;
                if (!isset($dynamicMedia[$fieldName])) {
                    $dynamicMedia[$fieldName] = [];
                }
                $dynamicMedia[$fieldName][] = [
                    'id' => $item->id,
                    'file_path' => asset($item->file_path),
                    'file_name' => $item->file_name,
                    'file_type' => $item->file_type,
                    'order' => $item->order,
                ];
            }

            // إذا كان في صورة واحدة فقط، نرجعها مباشرة
            foreach ($dynamicMedia as $fieldName => $images) {
                if (count($images) === 1) {
                    $dynamicMedia[$fieldName] = $images[0];
                }
            }
        }

        return $dynamicMedia;
    }
}
