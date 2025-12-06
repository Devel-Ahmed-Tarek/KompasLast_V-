<?php
namespace App\Http\Resources\Website;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TypeDitaliServicesResource extends JsonResource
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
            // الصور القديمة (للتوافق)
            'main_image'        => isset($this->main_image[$locale]) ? asset($this->main_image[$locale]) : null,
            'small_image'       => isset($this->small_image[$locale]) ? asset($this->small_image[$locale]) : null,
            
            // الصور الديناميكية (النظام الجديد)
            'media' => $dynamicMedia,
            
            // الحقول النصية
            'short_description' => $this->short_description,
            'feature_header'    => $this->feature_header,
            'feature_sub_title' => $this->feature_sub_title,
            'body'              => $this->body,
            'slug'              => $this->slug,
            'tips_title'        => $this->tips_title,
            'tips_subtitle'     => $this->tips_subtitle,
            'meta_keys'         => $this->meta_keys,
            'meta_title'        => $this->meta_title,
            'meta_description'  => $this->meta_description,
        ];
    }

    /**
     * جلب الصور الديناميكية للغة المحددة
     */
    private function getDynamicMedia($locale)
    {
        if (!$this->relationLoaded('media')) {
            return [];
        }

        $media = $this->media()
            ->where('language', $locale)
            ->orderBy('field_name')
            ->orderBy('order')
            ->get();

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
