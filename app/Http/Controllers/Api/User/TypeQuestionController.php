<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Http\Resources\QuestionResource;
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

        // Fetch questions for the given type_id
        // We only fetch main questions (where parent_question_id is null)
        // and eagerly load their options and child questions if needed (though existing structure suggests simple list or parent-child handled via next question logic, 
        // usually for a full list we might want everything or just the top level. 
        // usage indicates "send me the questions for the type", usually implies the full initial set or all of them.)
        // Let's stick to the pattern in OfferQuestionController: load main questions. 
        // BUT, if the user "wants the questions", they probably want to see the whole form setup? 
        // Postman request analysis showed one-by-one logic.
        // However, "send me the keys for the type" implies getting the schema. 
        // Let's return all questions ordered by 'order', eager loading options.
        
        $questions = TypeQuestion::where('type_id', $type_id)
            ->with(['options']) // Load options
            ->orderBy('order')
            ->get();

        if ($questions->isEmpty()) {
            return HelperFunc::sendResponse(200, 'No questions found for this type', []);
        }

        return HelperFunc::sendResponse(200, 'Questions retrieved successfully', [
            'type_id' => $type_id,
            'questions' => QuestionResource::collection($questions),
        ]);
    }
}
