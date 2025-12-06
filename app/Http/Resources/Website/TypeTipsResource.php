<?php

namespace App\Http\Resources\Website;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TypeTipsResource extends JsonResource
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
            'id' => $this->id,
            'name' => $this->name,
            'media' => $dynamicMedia, // الصور الديناميكية
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
