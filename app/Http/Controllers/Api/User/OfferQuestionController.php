<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Http\Resources\Website\QuestionResource;
use App\Mail\OfferCreated;
use App\Models\ConfigApp;
use App\Models\Offer;
use App\Models\OfferAnswer;
use App\Models\OfferAnswerFile;
use App\Models\Shopping_list;
use App\Models\TypeQuestion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Location\Facades\Location;

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

    /**
     * إرسال الفورم الكامل مع إنشاء Offer وجمع كل الإجابات
     */
    public function submitOfferForm(Request $request)
    {
        $lang = $request->get('lang', 'en');
        App::setLocale($lang);

        // Validation rules
        $validator = Validator::make($request->all(), [
            'type_id' => 'required|exists:types,id',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:type_questions,id',
            'answers.*.answer' => 'nullable|string',
            'answers.*.option_ids' => 'nullable|array',
            'answers.*.option_ids.*' => 'exists:question_options,id',
            'answers.*.files' => 'nullable|array',
            'answers.*.files.*' => 'file',

            // بيانات الـ Offer (optional - يمكن أن تكون موجودة أو لا)
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'date' => 'nullable|date',
            'adresse' => 'nullable|string|max:255',
            'ort' => 'nullable|string|max:255',
            'zimmer' => 'nullable|string|max:255',
            'etage' => 'nullable|string|max:255',
            'vorhanden' => 'nullable|string|max:255',
            'Nach_Adresse' => 'nullable|string|max:255',
            'Nach_Ort' => 'nullable|string|max:255',
            'Nach_Zimmer' => 'nullable|string|max:255',
            'Nach_Etage' => 'nullable|string|max:255',
            'Nach_vorhanden' => 'nullable|string|max:255',
            'count' => 'nullable|integer|min:0',
            'Besonderheiten' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'zipcode' => 'nullable|string|max:255',
            'Nach_country' => 'nullable|string|max:255',
            'Nach_zipcode' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->errors());
        }

        try {
            DB::beginTransaction();

            // معالجة form-data: جمع جميع question_ids من البيانات
            $typeId = $request->type_id;
            $questionIds = [];

            // جمع question_ids من answers
            if ($request->has('answers')) {
                $answers = $request->input('answers', []);
                foreach ($answers as $key => $answerData) {
                    if (is_array($answerData) && isset($answerData['question_id'])) {
                        $questionIds[] = $answerData['question_id'];
                    } elseif (is_numeric($key) && $request->has("answers.{$key}.question_id")) {
                        $questionIds[] = $request->input("answers.{$key}.question_id");
                    }
                }
            }

            $questionIds = array_unique($questionIds);

            $questions = TypeQuestion::whereIn('id', $questionIds)
                ->where('type_id', $typeId)
                ->get();

            if ($questions->count() !== count($questionIds)) {
                return HelperFunc::sendResponse(400, 'Some questions do not belong to this service type', []);
            }

            // إنشاء أو تحديث الـ Offer
            $offerData = [
                'type_id' => $typeId,
                'name' => $request->name ?? 'Guest User',
                'email' => $request->email ?? 'guest@example.com',
                'phone' => $request->phone ?? '',
                'date' => $request->date ?? now(),
                'adresse' => $request->adresse ?? '',
                'ort' => $request->ort ?? '',
                'zimmer' => $request->zimmer ?? '',
                'etage' => $request->etage ?? '',
                'zipcode' => $request->zipcode ?? '',
                'vorhanden' => $request->vorhanden ?? '',
                'Nach_Adresse' => $request->Nach_Adresse ?? null,
                'Nach_Ort' => $request->Nach_Ort ?? null,
                'Nach_Zimmer' => $request->Nach_Zimmer ?? null,
                'Nach_Etage' => $request->Nach_Etage ?? null,
                'Nach_vorhanden' => $request->Nach_vorhanden ?? null,
                'Nach_country' => $request->Nach_country ?? null,
                'Nach_zipcode' => $request->Nach_zipcode ?? null,
                'count' => $request->count ?? 1,
                'Number_of_offers' => $request->count ?? 1,
                'Besonderheiten' => $this->filterBesonderheiten($request->Besonderheiten ?? null),
                'ip' => $this->getClientIp($request),
                'country' => $request->country ?? $this->getCountryFromIP($this->getClientIp($request)),
                'city' => $request->city ?? $this->getCityFromIP($this->getClientIp($request)),
                'lang' => $lang,
                'cheek' => true,
                'completion_status' => 'completed',
            ];

            // التحقق من إعدادات النظام
            $config = ConfigApp::first();
            $status = true;
            if ($config && $config->offer_flow == 1) {
                $status = false;
            }

            if ($config && $config->add_offer == 1) {
                return HelperFunc::sendResponse(403, 'Offer creation is currently disabled', []);
            }

            $offerData['status'] = $status;

            // إنشاء الـ Offer
            $offer = Offer::create($offerData);

            // حفظ كل الإجابات مرتبة
            $savedAnswers = [];
            $allFiles = $request->allFiles(); // الحصول على جميع الملفات

            // معالجة answers من form-data
            $answersInput = $request->input('answers', []);

            foreach ($questionIds as $questionId) {
                $question = $questions->firstWhere('id', $questionId);

                if (!$question) {
                    continue;
                }

                // البحث عن بيانات الإجابة لهذا السؤال
                $answerText = null;
                $optionIds = [];
                $files = [];

                // البحث في answers array
                foreach ($answersInput as $key => $answerData) {
                    $currentQuestionId = null;

                    // الحصول على question_id
                    if (is_array($answerData) && isset($answerData['question_id'])) {
                        $currentQuestionId = $answerData['question_id'];
                    } elseif (is_numeric($key) && $request->has("answers.{$key}.question_id")) {
                        $currentQuestionId = $request->input("answers.{$key}.question_id");
                    }

                    // إذا كان هذا السؤال المطلوب
                    if ($currentQuestionId == $questionId) {
                        // الحصول على الإجابة النصية
                        if (is_array($answerData) && isset($answerData['answer'])) {
                            $answerText = $answerData['answer'];
                        } elseif ($request->has("answers.{$key}.answer")) {
                            $answerText = $request->input("answers.{$key}.answer");
                        }

                        // الحصول على option_ids
                        if (is_array($answerData) && isset($answerData['option_ids']) && is_array($answerData['option_ids'])) {
                            $optionIds = $answerData['option_ids'];
                        } elseif ($request->has("answers.{$key}.option_ids")) {
                            $optionIds = $request->input("answers.{$key}.option_ids", []);
                            if (!is_array($optionIds)) {
                                $optionIds = [$optionIds];
                            }
                        }

                        break;
                    }
                }

                // البحث عن الملفات لهذا السؤال من allFiles
                // Laravel يحول answers[28][files][0] إلى ['answers' => [28 => ['files' => [0 => file]]]]
                if (isset($allFiles['answers'])) {
                    foreach ($allFiles['answers'] as $key => $answerFiles) {
                        // التحقق من أن هذا السؤال
                        $fileQuestionId = null;

                        // محاولة الحصول على question_id من البيانات
                        if (is_array($answerFiles) && isset($answerFiles['question_id'])) {
                            // هذا غير ممكن لأن الملفات لا تحتوي على question_id
                        }

                        // محاولة الحصول على question_id من input
                        if ($request->has("answers.{$key}.question_id")) {
                            $fileQuestionId = $request->input("answers.{$key}.question_id");
                        } elseif (isset($answersInput[$key]['question_id'])) {
                            $fileQuestionId = $answersInput[$key]['question_id'];
                        }

                        if ($fileQuestionId == $questionId && isset($answerFiles['files'])) {
                            $files = is_array($answerFiles['files']) ? $answerFiles['files'] : [$answerFiles['files']];
                            break;
                        }
                    }
                }

                // محاولة أخرى: البحث مباشرة باستخدام hasFile
                if (empty($files)) {
                    foreach ($answersInput as $key => $answerData) {
                        $currentQuestionId = null;
                        if (is_array($answerData) && isset($answerData['question_id'])) {
                            $currentQuestionId = $answerData['question_id'];
                        }

                        if ($currentQuestionId == $questionId) {
                            // محاولة الحصول على الملفات باستخدام hasFile
                            if ($request->hasFile("answers.{$key}.files")) {
                                $files = $request->file("answers.{$key}.files");
                                if (!is_array($files)) {
                                    $files = [$files];
                                }
                                break;
                            }
                        }
                    }
                }

                // إنشاء أو تحديث الإجابة
                $answer = OfferAnswer::updateOrCreate(
                    [
                        'offer_id' => $offer->id,
                        'question_id' => $question->id
                    ],
                    [
                        'answer_text' => $answerText,
                    ]
                );

                // حفظ الاختيارات (options)
                if (!empty($optionIds)) {
                    $answer->options()->sync($optionIds);
                }

                // رفع الملفات إذا كانت موجودة
                if ($question->allows_file_upload && !empty($files)) {
                    // تصفية الملفات الصالحة فقط
                    $validFiles = array_filter($files, function ($file) {
                        return $file && $file->isValid();
                    });

                    if (!empty($validFiles)) {
                        $this->uploadAnswerFiles($answer, $validFiles, $question);
                    }
                }

                $savedAnswers[] = [
                    'question_id' => $question->id,
                    'answer_id' => $answer->id,
                ];
            }

            // تشغيل bayOffer إذا كان موجود
            $this->bayOffer($offer->id);

            // إرسال البريد الإلكتروني
            if ($request->email) {
                try {
                    Mail::to($request->email)->send(new OfferCreated($lang));
                } catch (\Exception $e) {
                    // تجاهل خطأ البريد الإلكتروني
                }
            }

            // إرسال إشعار للـ Admins
            try {
                $admins = User::where('role', 'admin')->where('available_notification', '1')->get();
                HelperFunc::sendMultilangNotification($admins, "new_offer_created", $offer->id, [
                    'en' => 'A new offer "' . $offer->name . '" has been created',
                    'de' => 'Ein neues Angebot "' . $offer->name . '" wurde erstellt',
                ]);
            } catch (\Exception $e) {
                // تجاهل خطأ الإشعارات
            }

            DB::commit();

            // جلب الـ Offer مع الإجابات
            $offer->load(['answers.question', 'answers.options', 'answers.files']);

            return HelperFunc::sendResponse(201, 'Offer and answers submitted successfully', [
                'offer' => [
                    'id' => $offer->id,
                    'type_id' => $offer->type_id,
                    'name' => $offer->name,
                    'email' => $offer->email,
                    'phone' => $offer->phone,
                    'completion_status' => $offer->completion_status,
                    'created_at' => $offer->created_at,
                ],
                'answers_count' => count($savedAnswers),
                'answers' => $offer->answers->map(function ($answer) use ($lang) {
                    return [
                        'question_id' => $answer->question_id,
                        'question_text' => $answer->question->getTranslation('question_text', $lang),
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
                }),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return HelperFunc::sendResponse(500, 'An error occurred: ' . $e->getMessage(), []);
        }
    }

    /**
     * Helper methods
     */
    private function getClientIp(Request $request)
    {
        $ip = $request->header('X-Forwarded-For');
        if ($ip) {
            $ip = explode(',', $ip)[0];
        } else {
            $ip = $request->ip();
        }
        return trim($ip);
    }

    private function getCountryFromIP($ip)
    {
        try {
            $position = Location::get($ip);
            return $position->countryName ?? 'Unknown';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    private function getCityFromIP($ip)
    {
        try {
            $position = Location::get($ip);
            return $position->cityName ?? 'Unknown';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    private function filterBesonderheiten($besonderheiten)
    {
        if (!$besonderheiten) {
            return null;
        }

        $patterns = [
            '/\b(\+?\d{1,3}[-.\s]?)?(\(?\d{1,4}\)?[-.\s]?)?\d{1,4}[-.\s]?\d{1,4}[-.\s]?\d{1,9}\b/',
            '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}\b/',
            '/\b\d+\s[A-Za-z]+\s(?:Street|St|Avenue|Ave|Road|Rd|Lane|Ln|Boulevard|Blvd|Drive|Dr|Court|Ct|Way|Square|Sq|Place|Pl|Terrace|Terr|Parkway|Pkwy)\b/i',
            '/\b\d+\s[A-Za-z]+\b/',
        ];

        return preg_replace($patterns, '*****', $besonderheiten);
    }

    private function bayOffer($offer_id)
    {
        try {
            $offer = Offer::with('type')->find($offer_id);

            if (!$offer || !$offer->type) {
                return;
            }

            $today = now()->format('Y-m-d');

            $companies = User::where('role', 'company')
                ->where('ban', '0')
                ->where('status', '1')
                ->whereHas('companyDetails', function ($query) {
                    $query->where('sucsses', '1');
                })
                ->whereHas('typesComapny', function ($query) use ($offer) {
                    $query->where('type_id', $offer->type_id);
                })
                ->withCount([
                    'shopping_list as shopping_list_count' => function ($query) use ($today) {
                        $query->where('type', 'D')
                            ->whereDate('created_at', $today);
                    },
                ])
                ->get();

            $companies->each(function ($company) {
                $company->shopping_list_count = $company->shopping_list_count ?? 0;
            });

            $filteredCompanies = $companies->filter(function ($company) use ($offer) {
                $amountTotal = $company->wallet->amount ?? 0;
                $expenseTotal = $company->wallet->expense ?? 0;
                $totalMoneyInWallet = $amountTotal - $expenseTotal;

                return $totalMoneyInWallet >= ($offer->type->price / $offer->Number_of_offers);
            });

            $sortedCompanies = $filteredCompanies->sortBy('shopping_list_count');

            foreach ($sortedCompanies as $company) {
                Shopping_list::create([
                    'offer_id' => $offer->id,
                    'user_id' => $company->id,
                    'type' => 'D',
                ]);
            }
        } catch (\Exception $e) {
            // تجاهل الأخطاء في bayOffer
        }
    }
}
