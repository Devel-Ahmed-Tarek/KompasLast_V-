<?php

namespace App\Http\Resources;

use App\Models\OfferFavorite;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferShopResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lang = $request->get('lang', 'en');
        
        // Check if this offer is in user's favorites
        $isFavorite = OfferFavorite::isFavorite($this->id);

        // تصفية الإجابات للأسئلة التي show_before_purchase = true فقط
        $answersBeforePurchase = [];
        if ($this->relationLoaded('answers')) {
            $answersBeforePurchase = $this->answers->filter(function ($answer) {
                return $answer->relationLoaded('question') &&
                    $answer->question &&
                    $answer->question->show_before_purchase === true;
            })->map(function ($answer) use ($lang) {
                $result = [
                    'id' => $answer->id,
                    'question_id' => $answer->question_id,
                    'question_text' => $answer->question->getTranslation('question_text', $lang),
                    'question_type' => $answer->question->question_type,
                    'answer_text' => $answer->answer_text,
                ];

                // إضافة الخيارات المحددة إذا كانت محملة
                if ($answer->relationLoaded('options')) {
                    $result['selected_options'] = $answer->options->map(function ($option) use ($lang) {
                        return [
                            'id' => $option->id,
                            'option_text' => $option->getTranslation('option_text', $lang),
                        ];
                    })->values();
                } else {
                    $result['selected_options'] = [];
                }

                // إضافة الملفات إذا كانت محملة
                if ($answer->relationLoaded('files')) {
                    $result['files'] = $answer->files->map(function ($file) {
                        return [
                            'id' => $file->id,
                            'file_name' => $file->file_name,
                            'file_type' => $file->file_type,
                            'file_url' => $file->file_url,
                            'file_size' => $file->file_size,
                            'mime_type' => $file->mime_type,
                        ];
                    })->values();
                } else {
                    $result['files'] = [];
                }

                return $result;
            })->values();
        }

        return [
            'type_id' => [
                'id'    => $this->type_id,
                'name'  => $this->type->getTranslation('name', $lang),
                'price' => $this->type->price / $this->Number_of_offers,
            ],
            'id'               => $this->id,
            'date'             => $this->date,
            'Number_of_offers' => $this->Number_of_offers,
            'count'            => $this->count,
            'status'           => $this->status,
            'is_favorite'      => $isFavorite,
            'answers'          => $answersBeforePurchase,
        ];
    }
}
