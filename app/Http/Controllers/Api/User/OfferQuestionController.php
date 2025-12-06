<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Http\Resources\QuestionResource;
use App\Models\Offer;
use App\Models\OfferAnswer;
use App\Models\OfferAnswerFile;
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
            'question' => new QuestionResource($firstQuestion, $lang),
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
            'question' => new QuestionResource($question->load('options'), $lang),
        ]);
    }

    /**
     * إرسال إجابة والحصول على السؤال التالي
     */
    public function submitAnswer(Request $request, $offer_id)
    {
        $offer = Offer::with('type')->findOrFail($offer_id);
        $lang = $request->get('lang', 'en');
        App::setLocale($lang);

        $question = TypeQuestion::findOrFail($request->question_id);

        // التحقق من أن السؤال ينتمي لنفس الـ Type
        if ($question->type_id != $offer->type_id) {
            return HelperFunc::sendResponse(400, 'Question does not belong to this offer type', []);
        }

        // Validation rules
        $rules = [
            'question_id' => 'required|exists:type_questions,id',
            'answer' => 'nullable|string',
            'option_ids' => 'nullable|array',
            'option_ids.*' => 'exists:question_options,id',
        ];

        // إذا كان السؤال يسمح برفع الملفات
        if ($question->allows_file_upload) {
            $maxFiles = $question->max_files ?? 10;
            $maxSize = ($question->max_file_size ?? 10) * 1024; // تحويل من MB إلى KB

            $rules['files'] = 'nullable|array|max:' . $maxFiles;
            $rules['files.*'] = 'file|max:' . $maxSize;
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->errors());
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

        // رفع الملفات إذا كان السؤال يسمح بذلك
        if ($question->allows_file_upload && $request->hasFile('files')) {
            $this->uploadAnswerFiles($answer, $request->file('files'), $question);
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
            $response['next_question'] = new QuestionResource($nextQuestion->load('options'), $lang);
        }

        return HelperFunc::sendResponse(200, 'Answer submitted successfully', $response);
    }

    /**
     * جلب كل الإجابات للـ Offer
     */
    public function getAnswers(Request $request, $offer_id)
    {
        $offer = Offer::with(['answers.question', 'answers.options', 'answers.files'])->findOrFail($offer_id);
        $lang = $request->get('lang', 'en');
        App::setLocale($lang);

        $answers = $offer->answers->map(function ($answer) use ($lang) {
            return [
                'question_id' => $answer->question_id,
                'question_text' => $answer->question->getTranslation('question_text', $lang),
                'question_type' => $answer->question->question_type,
                'answer_text' => $answer->answer_text,
                'selected_options' => $answer->options->map(function ($option) use ($lang) {
                    return [
                        'id' => $option->id,
                        'option_text' => $option->getTranslation('option_text', $lang),
                    ];
                }),
                'files' => $answer->files->map(function ($file) {
                    return [
                        'id' => $file->id,
                        'file_name' => $file->file_name,
                        'file_type' => $file->file_type,
                        'file_url' => $file->file_url,
                        'file_size' => $file->file_size,
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
     * رفع ملفات للإجابة
     */
    public function uploadFiles(Request $request, $offer_id, $answer_id)
    {
        $validator = Validator::make($request->all(), [
            'files' => 'required|array',
            'files.*' => 'required|file|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->errors());
        }

        $offer = Offer::findOrFail($offer_id);
        $answer = OfferAnswer::where('offer_id', $offer_id)
            ->with('question')
            ->findOrFail($answer_id);

        $question = $answer->question;

        // التحقق من أن السؤال يسمح برفع الملفات
        if (!$question->allows_file_upload) {
            return HelperFunc::sendResponse(400, 'This question does not allow file uploads', []);
        }

        // التحقق من عدد الملفات
        $currentFilesCount = $answer->files()->count();
        $maxFiles = $question->max_files ?? 10;

        if ($currentFilesCount + count($request->file('files')) > $maxFiles) {
            return HelperFunc::sendResponse(400, "Maximum files limit exceeded. You can upload up to {$maxFiles} files.", []);
        }

        // رفع الملفات
        $uploadedFiles = $this->uploadAnswerFiles($answer, $request->file('files'), $question);

        return HelperFunc::sendResponse(200, 'Files uploaded successfully', [
            'uploaded_files' => $uploadedFiles,
            'total_files' => $answer->files()->count(),
        ]);
    }

    /**
     * حذف ملف من الإجابة
     */
    public function deleteFile(Request $request, $offer_id, $answer_id, $file_id)
    {
        $offer = Offer::findOrFail($offer_id);
        $answer = OfferAnswer::where('offer_id', $offer_id)->findOrFail($answer_id);
        $file = OfferAnswerFile::where('offer_answer_id', $answer_id)->findOrFail($file_id);

        // حذف الملف من السيرفر
        if (file_exists($file->file_path)) {
            unlink($file->file_path);
        }

        // حذف السجل من قاعدة البيانات
        $file->delete();

        return HelperFunc::sendResponse(200, 'File deleted successfully', []);
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

    /**
     * رفع ملفات الإجابة
     */
    private function uploadAnswerFiles($answer, $files, $question)
    {
        $allowedTypes = $question->allowed_file_types
            ? explode(',', $question->allowed_file_types)
            : ['image', 'video', 'document'];

        $uploadedFiles = [];

        foreach ($files as $file) {
            // تحديد نوع الملف
            $fileType = $this->getFileType($file);

            // التحقق من نوع الملف المسموح
            if (!in_array($fileType, $allowedTypes)) {
                continue; // تخطي الملفات غير المسموحة
            }

            // رفع الملف
            $filePath = HelperFunc::uploadFile('offer-answers', $file);

            // حفظ معلومات الملف
            $uploadedFile = OfferAnswerFile::create([
                'offer_answer_id' => $answer->id,
                'file_path' => $filePath,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $fileType,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);

            $uploadedFiles[] = [
                'id' => $uploadedFile->id,
                'file_name' => $uploadedFile->file_name,
                'file_type' => $uploadedFile->file_type,
                'file_url' => $uploadedFile->file_url,
                'file_size' => $uploadedFile->file_size,
            ];
        }

        return $uploadedFiles;
    }

    /**
     * تحديد نوع الملف
     */
    private function getFileType($file)
    {
        $mimeType = $file->getMimeType();

        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        } elseif (str_starts_with($mimeType, 'video/')) {
            return 'video';
        } else {
            return 'document';
        }
    }
}
