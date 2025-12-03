<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\OfferAnswer;
use App\Models\TypeQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

class OfferQuestionController extends Controller
{
    /**
     * جلب أول سؤال للـ Offer
     */
    public function getFirstQuestion(Request $request, $offer_id)
    {
        $offer = Offer::with('type')->findOrFail($offer_id);
        $lang = $request->get('lang', 'en');
        
        // Set locale
        App::setLocale($lang);
        
        // جلب أول سؤال رئيسي
        $firstQuestion = TypeQuestion::where('type_id', $offer->type_id)
            ->whereNull('parent_question_id')
            ->with('options')
            ->orderBy('order')
            ->first();
            
        if (!$firstQuestion) {
            return HelperFunc::sendResponse(404, 'No questions found for this service', []);
        }
        
        $totalQuestions = TypeQuestion::where('type_id', $offer->type_id)
            ->whereNull('parent_question_id')
            ->count();
            
        $answeredCount = $offer->answers()->count();
        
        return HelperFunc::sendResponse(200, 'First question retrieved successfully', [
            'question' => [
                'id' => $firstQuestion->id,
                'question_text' => $firstQuestion->getTranslation('question_text', $lang),
                'question_type' => $firstQuestion->question_type,
                'is_required' => $firstQuestion->is_required,
                'options' => $firstQuestion->options->map(function($option) use ($lang) {
                    return [
                        'id' => $option->id,
                        'option_text' => $option->getTranslation('option_text', $lang),
                        'order' => $option->order,
                    ];
                }),
            ],
            'total_questions' => $totalQuestions,
            'answered_count' => $answeredCount,
            'progress' => [
                'answered' => $answeredCount,
                'total' => $totalQuestions,
                'percentage' => $totalQuestions > 0 ? round(($answeredCount / $totalQuestions) * 100, 2) : 0,
            ],
        ]);
    }

    /**
     * جلب سؤال معين
     */
    public function getQuestion(Request $request, $offer_id, $question_id)
    {
        $offer = Offer::findOrFail($offer_id);
        $lang = $request->get('lang', 'en');
        App::setLocale($lang);
        
        $question = TypeQuestion::where('type_id', $offer->type_id)
            ->with('options')
            ->findOrFail($question_id);
        
        return HelperFunc::sendResponse(200, 'Question retrieved successfully', [
            'question' => [
                'id' => $question->id,
                'question_text' => $question->getTranslation('question_text', $lang),
                'question_type' => $question->question_type,
                'is_required' => $question->is_required,
                'options' => $question->options->map(function($option) use ($lang) {
                    return [
                        'id' => $option->id,
                        'option_text' => $option->getTranslation('option_text', $lang),
                        'order' => $option->order,
                    ];
                }),
            ],
        ]);
    }

    /**
     * إرسال إجابة والحصول على السؤال التالي
     */
    public function submitAnswer(Request $request, $offer_id)
    {
        $validator = Validator::make($request->all(), [
            'question_id' => 'required|exists:type_questions,id',
            'answer' => 'nullable|string',
            'option_ids' => 'nullable|array',
            'option_ids.*' => 'exists:question_options,id',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->errors());
        }

        $offer = Offer::with('type')->findOrFail($offer_id);
        $lang = $request->get('lang', 'en');
        App::setLocale($lang);
        
        $question = TypeQuestion::findOrFail($request->question_id);
        
        // التحقق من أن السؤال ينتمي لنفس الـ Type
        if ($question->type_id != $offer->type_id) {
            return HelperFunc::sendResponse(400, 'Question does not belong to this offer type', []);
        }
        
        // حفظ الإجابة
        $answer = OfferAnswer::updateOrCreate(
            [
                'offer_id' => $offer_id,
                'question_id' => $question->id
            ],
            [
                'answer_text' => $request->answer ?? null,
            ]
        );
        
        // حفظ الاختيارات
        if ($request->has('option_ids') && !empty($request->option_ids)) {
            $answer->options()->sync($request->option_ids);
        }
        
        // البحث عن السؤال التالي
        $nextQuestion = $this->getNextQuestion($offer, $question, $request->option_ids ?? []);
        
        // تحديث حالة الـ Offer
        if ($nextQuestion) {
            $offer->update(['completion_status' => 'in_progress']);
        } else {
            $offer->update(['completion_status' => 'completed']);
        }
        
        // حساب التقدم
        $progress = $this->calculateProgress($offer);
        
        // إرجاع السؤال التالي مع الترجمة
        $response = [
            'is_completed' => !$nextQuestion,
            'progress' => $progress,
        ];
        
        if ($nextQuestion) {
            $response['next_question'] = [
                'id' => $nextQuestion->id,
                'question_text' => $nextQuestion->getTranslation('question_text', $lang),
                'question_type' => $nextQuestion->question_type,
                'is_required' => $nextQuestion->is_required,
                'options' => $nextQuestion->options->map(function($option) use ($lang) {
                    return [
                        'id' => $option->id,
                        'option_text' => $option->getTranslation('option_text', $lang),
                        'order' => $option->order,
                    ];
                }),
            ];
        }
        
        return HelperFunc::sendResponse(200, 'Answer submitted successfully', $response);
    }

    /**
     * جلب كل الإجابات للـ Offer
     */
    public function getAnswers(Request $request, $offer_id)
    {
        $offer = Offer::with(['answers.question', 'answers.options'])->findOrFail($offer_id);
        $lang = $request->get('lang', 'en');
        App::setLocale($lang);
        
        $answers = $offer->answers->map(function($answer) use ($lang) {
            return [
                'question_id' => $answer->question_id,
                'question_text' => $answer->question->getTranslation('question_text', $lang),
                'question_type' => $answer->question->question_type,
                'answer_text' => $answer->answer_text,
                'selected_options' => $answer->options->map(function($option) use ($lang) {
                    return [
                        'id' => $option->id,
                        'option_text' => $option->getTranslation('option_text', $lang),
                    ];
                }),
            ];
        });
        
        return HelperFunc::sendResponse(200, 'Answers retrieved successfully', [
            'offer_id' => $offer->id,
            'completion_status' => $offer->completion_status,
            'answers' => $answers,
            'progress' => $this->calculateProgress($offer),
        ]);
    }

    /**
     * تعديل إجابة موجودة
     */
    public function updateAnswer(Request $request, $offer_id, $answer_id)
    {
        $validator = Validator::make($request->all(), [
            'answer' => 'nullable|string',
            'option_ids' => 'nullable|array',
            'option_ids.*' => 'exists:question_options,id',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->errors());
        }

        $offer = Offer::findOrFail($offer_id);
        $answer = OfferAnswer::where('offer_id', $offer_id)->findOrFail($answer_id);
        
        $answer->answer_text = $request->answer ?? $answer->answer_text;
        $answer->save();
        
        if ($request->has('option_ids')) {
            $answer->options()->sync($request->option_ids);
        }
        
        return HelperFunc::sendResponse(200, 'Answer updated successfully', [
            'answer' => $answer,
            'progress' => $this->calculateProgress($offer),
        ]);
    }

    /**
     * البحث عن السؤال التالي
     */
    private function getNextQuestion($offer, $currentQuestion, $selectedOptionIds)
    {
        // 1. البحث عن سؤال متفرع (branching)
        if (!empty($selectedOptionIds)) {
            $branchQuestion = TypeQuestion::where('type_id', $offer->type_id)
                ->where('parent_question_id', $currentQuestion->id)
                ->whereIn('parent_option_id', $selectedOptionIds)
                ->with('options')
                ->orderBy('order')
                ->first();
                
            if ($branchQuestion) {
                return $branchQuestion;
            }
        }
        
        // 2. لو مش موجود branching، نرجع للسؤال اللي بعده في الـ order
        $nextQuestion = TypeQuestion::where('type_id', $offer->type_id)
            ->whereNull('parent_question_id') // أسئلة رئيسية فقط
            ->where('order', '>', $currentQuestion->order)
            ->with('options')
            ->orderBy('order')
            ->first();
            
        return $nextQuestion;
    }

    /**
     * حساب التقدم
     */
    private function calculateProgress($offer)
    {
        $totalQuestions = TypeQuestion::where('type_id', $offer->type_id)
            ->whereNull('parent_question_id')
            ->count();
            
        $answeredQuestions = $offer->answers()->count();
        
        return [
            'answered' => $answeredQuestions,
            'total' => $totalQuestions,
            'percentage' => $totalQuestions > 0 ? round(($answeredQuestions / $totalQuestions) * 100, 2) : 0,
        ];
    }
}
