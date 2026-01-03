<?php

namespace App\Http\Resources\Website;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    protected $lang;
    protected $includeChildren;

    public function __construct($resource, $lang = 'en', $includeChildren = false)
    {
        parent::__construct($resource);
        $this->lang = $lang;
        $this->includeChildren = $includeChildren;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lang = $request->get('lang', $this->lang ?? 'en');

        $hasOptions = in_array($this->question_type, ['single_choice', 'multi_choice']);
        $isFileQuestion = $this->question_type === 'file' || $this->allows_file_upload;

        return [
            'id' => $this->id,
            'question_text' => $this->getTranslation('question_text', $lang),
            'question_type' => $this->question_type,
            'is_required' => $this->is_required,

            // معلومات الملفات
            'allows_file_upload' => $this->allows_file_upload,
            'is_file_question' => $isFileQuestion, // ✅ هل السؤال من نوع ملف؟
            'allowed_file_types' => $this->allowed_file_types ? explode(',', $this->allowed_file_types) : null,
            'max_files' => $this->max_files,
            'max_file_size' => $this->max_file_size, // بالـ MB

            // معلومات الـ Options
            'has_options' => $hasOptions, // ✅ هل السؤال له options؟
            'options' => $this->whenLoaded('options', function () use ($lang) {
                return $this->options->map(function ($option) use ($lang) {
                    return [
                        'id' => $option->id,
                        'option_text' => $option->getTranslation('option_text', $lang),
                        'order' => $option->order,
                        'icon' => $option->icon ? asset($option->icon) : null,
                    ];
                });
            }, []),
            'options_count' => $this->whenLoaded('options', function () {
                return $this->options->count();
            }, 0),

            // معلومات إضافية
            'order' => $this->order,
            'parent_question_id' => $this->parent_question_id,
            'parent_option_id' => $this->parent_option_id,

            // Child Questions (nested) - فقط إذا كان includeChildren = true
            'children' => $this->when($this->includeChildren && $this->relationLoaded('childQuestions'), function () use ($lang, $request) {
                return $this->childQuestions->map(function ($childQuestion) use ($lang, $request) {
                    $childResource = new QuestionResource($childQuestion, $lang, false); // لا نضيف children للـ children
                    return $childResource->toArray($request);
                });
            }, []),
        ];
    }
}

