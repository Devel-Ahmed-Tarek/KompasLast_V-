<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Http\Resources\Website\QuestionResource;
use App\Models\TypeQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class TypeQuestionController extends Controller
{
    /**
     * Get all questions for a specific type.
     *
     * @param Request $request
     * @param int $type_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQuestions(Request $request, $type_id)
    {
        $lang = $request->get('lang', 'en');
        App::setLocale($lang);

        // جلب الأسئلة الرئيسية فقط (parent questions)
        $mainQuestions = TypeQuestion::where('type_id', $type_id)
            ->whereNull('parent_question_id') // الأسئلة الرئيسية فقط
            ->with([
                'options', // Load options
                'childQuestions.options', // Load child questions with their options
            ])
            ->orderBy('order')
            ->get();

        // استخدام QuestionResource مع includeChildren = true
        $questions = $mainQuestions->map(function ($question) use ($lang) {
            $resource = new QuestionResource($question, $lang, true);
            return $resource->resolve(request());
        });

        if ($mainQuestions->isEmpty()) {
            return HelperFunc::sendResponse(200, 'No questions found for this type', [
                'questions' => [],
            ]);
        }

        return HelperFunc::sendResponse(200, 'Questions retrieved successfully', [
            'questions' => $questions,
        ]);
    }
}
