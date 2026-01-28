<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferAnswerResource extends JsonResource
{
    protected $lang;

    public function __construct($resource, $lang = 'en')
    {
        parent::__construct($resource);
        $this->lang = $lang;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get language from Accept-Language header or query param, default to 'en'
        $lang = $request->header('Accept-Language', $request->get('lang', $this->lang ?? 'en'));
        $lang = substr($lang, 0, 2); // Take only first 2 characters (e.g., "en-US" → "en")
        
        return [
            'id' => $this->id,
            'question_id' => $this->question_id,
            'question_text' => $this->question->getTranslation('question_text', $lang),
            'question_type' => $this->question->question_type,
            'answer_text' => $this->answer_text, // إجابة المستخدم (غير قابلة للترجمة)
            'selected_options' => $this->options->map(function($option) use ($lang) {
                return [
                    'id' => $option->id,
                    'option_text' => $option->getTranslation('option_text', $lang),
                ];
            }),
            'files' => $this->whenLoaded('files', function() {
                return $this->files->map(function($file) {
                    return [
                        'id' => $file->id,
                        'file_name' => $file->file_name,
                        'file_type' => $file->file_type,
                        'file_url' => $file->file_url,
                        'file_size' => $file->file_size,
                        'mime_type' => $file->mime_type,
                    ];
                });
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
