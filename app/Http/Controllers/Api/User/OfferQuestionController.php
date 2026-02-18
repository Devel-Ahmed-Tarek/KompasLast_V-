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
use Illuminate\Support\Facades\Log;
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
            // التحقق من الملفات
            $filesCount = $answer->files->count();
            if ($filesCount > 0) {
                Log::info("Answer {$answer->id} has {$filesCount} files", [
                    'answer_id' => $answer->id,
                    'question_id' => $answer->question_id,
                    'files' => $answer->files->pluck('file_name')->toArray()
                ]);
            }

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
            // التحقق من أن الملف موجود وصالح
            if (!$file) {
                Log::warning('File is null');
                continue;
            }

            $fileName = $file->getClientOriginalName() ?? 'unknown';

            // التحقق من أن الملف صالح
            if (!$file->isValid()) {
                Log::warning('File is not valid: ' . $fileName);
                continue;
            }

            // الحصول على المسار الحقيقي للملف
            $realPath = null;
            try {
                $realPath = $file->getRealPath();
            } catch (\Exception $e) {
                Log::warning('Cannot get real path: ' . $e->getMessage());
            }

            // التحقق من أن الملف قابل للقراءة
            if (!$realPath || !is_readable($realPath)) {
                Log::warning('File is not readable: ' . ($realPath ?? 'unknown path') . ' - Original: ' . $fileName);

                // محاولة استخدام getPathname بدلاً من getRealPath
                try {
                    $pathname = $file->getPathname();
                    if ($pathname && is_readable($pathname)) {
                        $realPath = $pathname;
                        Log::info('Using pathname instead: ' . $pathname);
                    } else {
                        continue;
                    }
                } catch (\Exception $e) {
                    Log::error('Cannot get pathname: ' . $e->getMessage());
                    continue;
                }
            }

            try {
                // قراءة محتوى الملف فوراً قبل أن يتم حذفه من /tmp
                $fileContent = null;
                $fileSize = null;
                $mimeType = null;

                try {
                    Log::info('Reading file content immediately', [
                        'file_name' => $fileName,
                        'real_path' => $realPath,
                        'file_size' => $file->getSize()
                    ]);

                    $fileContent = file_get_contents($realPath);
                    if ($fileContent === false) {
                        Log::error('Cannot read file content: ' . $realPath);
                        continue;
                    }

                    $fileSize = strlen($fileContent);
                    $mimeType = $file->getMimeType();

                    Log::info('File content read successfully', [
                        'file_name' => $fileName,
                        'content_size' => $fileSize,
                        'mime_type' => $mimeType
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error reading file content: ' . $e->getMessage());
                    continue;
                }

                // تحديد نوع الملف
                $fileType = $this->getFileType($file);

                // التحقق من نوع الملف المسموح
                if (!in_array($fileType, $allowedTypes)) {
                    Log::info('File type not allowed: ' . $fileType);
                    continue; // تخطي الملفات غير المسموحة
                }

                // حفظ الملف مباشرة من المحتوى بدلاً من استخدام move()
                $extension = strtolower($file->getClientOriginalExtension() ?: pathinfo($fileName, PATHINFO_EXTENSION));
                $name = time() . rand(100, 999) . '.' . $extension;
                $destinationPath = 'uploads/offer-answers';
                $destination = public_path($destinationPath . '/' . $name);

                // إنشاء المجلد إذا لم يكن موجوداً
                if (!file_exists(public_path($destinationPath))) {
                    mkdir(public_path($destinationPath), 0755, true);
                }

                Log::info('Writing file content to destination', [
                    'file_name' => $fileName,
                    'destination' => $destination,
                    'content_size' => $fileSize
                ]);

                // كتابة المحتوى مباشرة
                $written = file_put_contents($destination, $fileContent);

                if ($written === false || $written !== $fileSize) {
                    Log::error('Failed to write file content', [
                        'file_name' => $fileName,
                        'destination' => $destination,
                        'expected_size' => $fileSize,
                        'written_size' => $written
                    ]);
                    continue;
                }

                $filePath = $destinationPath . '/' . $name;

                Log::info('File written successfully', [
                    'file_name' => $fileName,
                    'file_path' => $filePath,
                    'file_size' => $fileSize,
                    'written_size' => $written
                ]);

                // التحقق من أن الملف تم حفظه بنجاح
                if (!file_exists(public_path($filePath))) {
                    Log::error('File does not exist after write: ' . $filePath);
                    continue;
                }

                // حفظ معلومات الملف
                Log::info('Saving file to database', [
                    'offer_answer_id' => $answer->id,
                    'file_path' => $filePath,
                    'file_name' => $fileName,
                    'file_type' => $fileType,
                    'mime_type' => $mimeType,
                    'file_size' => $fileSize,
                    'file_size_mb' => round($fileSize / 1024 / 1024, 2)
                ]);

                $uploadedFile = OfferAnswerFile::create([
                    'offer_answer_id' => $answer->id,
                    'file_path' => $filePath,
                    'file_name' => $fileName,
                    'file_type' => $fileType,
                    'mime_type' => $mimeType,
                    'file_size' => $fileSize,
                ]);

                Log::info('File saved to database successfully', [
                    'file_id' => $uploadedFile->id,
                    'offer_answer_id' => $answer->id,
                    'file_path' => $filePath,
                    'file_name' => $uploadedFile->file_name,
                    'file_url' => $uploadedFile->file_url,
                    'file_type' => $uploadedFile->file_type,
                    'file_size' => $uploadedFile->file_size,
                    'database_record' => $uploadedFile->toArray()
                ]);

                $uploadedFiles[] = [
                    'id' => $uploadedFile->id,
                    'file_name' => $uploadedFile->file_name,
                    'file_type' => $uploadedFile->file_type,
                    'file_url' => $uploadedFile->file_url,
                    'file_size' => $uploadedFile->file_size,
                ];
            } catch (\Exception $e) {
                Log::error('Error uploading file: ' . $e->getMessage());
                continue; // تخطي الملفات التي فشل رفعها
            }
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
        $config = ConfigApp::first();

        if ($config->offer_flow == 1) {
            $status = false;
        }

        if ($config->add_offer == 1) {
            return HelperFunc::apiResponse(true, 200, ['message' => 'Offer Add Is Stoping']);
        }
        // Validation rules
        $validator = Validator::make($request->all(), [
            'type_id' => 'required|exists:types,id',
            'country_id' => 'required|exists:countries,id',
            'city_id' => 'required|exists:cities,id',
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

        // Validate that city belongs to country
        if ($request->has('country_id') && $request->has('city_id')) {
            $city = \App\Models\City::find($request->city_id);
            if ($city && $city->country_id != $request->country_id) {
                return HelperFunc::sendResponse(422, 'Validation Error', [
                    'city_id' => ['The selected city does not belong to the selected country.']
                ]);
            }
        }

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->errors());
        }

        try {
            DB::beginTransaction();

            Log::info('=== Starting Offer Form Submission ===', [
                'type_id' => $request->type_id,
                'name' => $request->name,
                'email' => $request->email,
                'lang' => $lang
            ]);

            // معالجة form-data: جمع جميع question_ids من البيانات
            $typeId = $request->type_id;
            $questionIds = [];

            // جمع question_ids من answers
            if ($request->has('answers')) {
                $answers = $request->input('answers', []);
                Log::info('Answers input received', [
                    'answers_count' => count($answers),
                    'answers_keys' => array_keys($answers)
                ]);

                foreach ($answers as $key => $answerData) {
                    if (is_array($answerData) && isset($answerData['question_id'])) {
                        $questionIds[] = $answerData['question_id'];
                    } elseif (is_numeric($key) && $request->has("answers.{$key}.question_id")) {
                        $questionIds[] = $request->input("answers.{$key}.question_id");
                    }
                }
            }

            $questionIds = array_unique($questionIds);
            Log::info('Question IDs collected', [
                'question_ids' => $questionIds,
                'count' => count($questionIds)
            ]);

            $questions = TypeQuestion::whereIn('id', $questionIds)
                ->where('type_id', $typeId)
                ->get();

            if ($questions->count() !== count($questionIds)) {
                return HelperFunc::sendResponse(400, 'Some questions do not belong to this service type', []);
            }

            // إنشاء أو تحديث الـ Offer
            $offerData = [
                'type_id' => $typeId,
                'country_id' => $request->country_id,
                'city_id' => $request->city_id,
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

            $offerData['status']         = $status;
            $offerData['confirm_status'] = 'pending';
            $offerData['confirm_token']  = \Illuminate\Support\Str::random(64);
            $offerData['confirmed_at']   = null;

            Log::info('=== Creating Offer ===', [
                'offer_data' => $offerData,
                'status' => $status
            ]);

            // إنشاء الـ Offer
            $offer = Offer::create($offerData);

            Log::info('Offer created successfully', [
                'offer_id' => $offer->id,
                'type_id' => $offer->type_id,
                'name' => $offer->name,
                'email' => $offer->email,
                'phone' => $offer->phone,
                'completion_status' => $offer->completion_status,
                'created_at' => $offer->created_at
            ]);

            // حفظ كل الإجابات مرتبة
            $savedAnswers = [];
            $allFiles = $request->allFiles(); // الحصول على جميع الملفات

            Log::info('=== Processing Answers ===', [
                'offer_id' => $offer->id,
                'total_questions' => count($questionIds),
                'allFiles_keys' => array_keys($allFiles),
                'allFiles_structure' => array_map(function ($key) use ($allFiles) {
                    return [
                        'key' => $key,
                        'type' => gettype($allFiles[$key] ?? null),
                        'is_array' => is_array($allFiles[$key] ?? null)
                    ];
                }, array_keys($allFiles))
            ]);

            // معالجة answers من form-data
            $answersInput = $request->input('answers', []);

            Log::info('Answers input structure', [
                'answers_count' => count($answersInput),
                'answers_keys' => array_keys($answersInput),
                'answers_sample' => array_slice($answersInput, 0, 3, true) // أول 3 إجابات فقط للعرض
            ]);

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

                        // محاولة الحصول على question_id من input
                        if ($request->has("answers.{$key}.question_id")) {
                            $fileQuestionId = $request->input("answers.{$key}.question_id");
                        } elseif (isset($answersInput[$key]) && is_array($answersInput[$key]) && isset($answersInput[$key]['question_id'])) {
                            $fileQuestionId = $answersInput[$key]['question_id'];
                        }

                        if ($fileQuestionId == $questionId && isset($answerFiles['files'])) {
                            $files = is_array($answerFiles['files']) ? $answerFiles['files'] : [$answerFiles['files']];
                            Log::info("Found files for question {$questionId} using allFiles method", ['count' => count($files)]);
                            break;
                        }
                    }
                }

                // محاولة أخرى: البحث مباشرة باستخدام hasFile مع dot notation
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
                                Log::info("Found files for question {$questionId} using dot notation", ['count' => count($files)]);
                                break;
                            }

                            // محاولة أخرى: البحث باستخدام array notation
                            if ($request->hasFile("answers[{$key}][files]")) {
                                $files = $request->file("answers[{$key}][files]");
                                if (!is_array($files)) {
                                    $files = [$files];
                                }
                                Log::info("Found files for question {$questionId} using array notation", ['count' => count($files)]);
                                break;
                            }
                        }
                    }
                }

                // محاولة أخيرة: البحث في جميع الملفات باستخدام question_id مباشرة
                if (empty($files) && isset($allFiles['answers'])) {
                    // البحث في جميع المفاتيح
                    foreach ($allFiles['answers'] as $key => $answerFiles) {
                        // الحصول على question_id من input
                        $fileQuestionId = null;
                        if (isset($answersInput[$key]) && is_array($answersInput[$key]) && isset($answersInput[$key]['question_id'])) {
                            $fileQuestionId = $answersInput[$key]['question_id'];
                        } elseif ($request->has("answers.{$key}.question_id")) {
                            $fileQuestionId = $request->input("answers.{$key}.question_id");
                        }

                        if ($fileQuestionId == $questionId && isset($answerFiles['files'])) {
                            $files = is_array($answerFiles['files']) ? $answerFiles['files'] : [$answerFiles['files']];
                            Log::info("Found files for question {$questionId} using final method", ['count' => count($files)]);
                            break;
                        }
                    }
                }

                if (empty($files) && $question->allows_file_upload) {
                    Log::warning("No files found for question {$questionId} even though file upload is allowed", [
                        'allFiles_keys' => array_keys($allFiles['answers'] ?? []),
                        'answersInput_keys' => array_keys($answersInput)
                    ]);
                }

                Log::info('=== Processing Answer ===', [
                    'question_id' => $question->id,
                    'question_text' => $question->getTranslation('question_text', 'en'),
                    'question_type' => $question->question_type,
                    'answer_text' => $answerText,
                    'option_ids' => $optionIds,
                    'files_count' => count($files),
                    'allows_file_upload' => $question->allows_file_upload
                ]);

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

                Log::info('Answer saved', [
                    'answer_id' => $answer->id,
                    'offer_id' => $answer->offer_id,
                    'question_id' => $answer->question_id,
                    'answer_text' => $answer->answer_text
                ]);

                // حفظ الاختيارات (options)
                if (!empty($optionIds)) {
                    $answer->options()->sync($optionIds);
                }

                // رفع الملفات إذا كانت موجودة
                if ($question->allows_file_upload && !empty($files)) {
                    Log::info('=== Processing Files for Question ===', [
                        'question_id' => $question->id,
                        'question_text' => $question->getTranslation('question_text', 'en'),
                        'files_count' => count($files),
                        'allows_file_upload' => $question->allows_file_upload,
                        'allowed_file_types' => $question->allowed_file_types,
                        'max_files' => $question->max_files,
                        'max_file_size' => $question->max_file_size
                    ]);

                    // تسجيل معلومات كل ملف قبل التصفية
                    foreach ($files as $index => $file) {
                        if ($file) {
                            Log::info("File #{$index} Details", [
                                'file_name' => $file->getClientOriginalName(),
                                'file_size' => $file->getSize(),
                                'mime_type' => $file->getMimeType(),
                                'is_valid' => $file->isValid(),
                                'real_path' => $file->getRealPath() ?? 'N/A',
                                'pathname' => $file->getPathname() ?? 'N/A',
                                'is_readable' => $file->getRealPath() ? is_readable($file->getRealPath()) : false
                            ]);
                        } else {
                            Log::warning("File #{$index} is null");
                        }
                    }

                    // تصفية الملفات الصالحة فقط
                    $validFiles = array_filter($files, function ($file) use ($question) {
                        if (!$file) {
                            Log::warning('File is null, skipping');
                            return false;
                        }

                        $fileName = $file->getClientOriginalName() ?? 'unknown';

                        // التحقق من أن الملف صالح
                        if (!$file->isValid()) {
                            Log::warning('Invalid file', [
                                'file_name' => $fileName,
                                'error_code' => $file->getError(),
                                'error_message' => $file->getErrorMessage()
                            ]);
                            return false;
                        }

                        // التحقق من أن الملف قابل للقراءة
                        try {
                            $realPath = $file->getRealPath();
                            if (!$realPath || !is_readable($realPath)) {
                                Log::warning('File is not readable', [
                                    'file_name' => $fileName,
                                    'real_path' => $realPath ?? 'N/A',
                                    'is_readable' => $realPath ? is_readable($realPath) : false
                                ]);
                                return false;
                            }

                            // التحقق من حجم الملف
                            $fileSize = $file->getSize();
                            $maxSize = ($question->max_file_size ?? 10) * 1024 * 1024; // تحويل من MB إلى bytes
                            if ($fileSize > $maxSize) {
                                Log::warning('File size exceeds maximum', [
                                    'file_name' => $fileName,
                                    'file_size' => $fileSize,
                                    'max_size' => $maxSize,
                                    'file_size_mb' => round($fileSize / 1024 / 1024, 2),
                                    'max_size_mb' => $question->max_file_size
                                ]);
                                return false;
                            }

                            Log::info('File is valid and ready for upload', [
                                'file_name' => $fileName,
                                'file_size' => $fileSize,
                                'file_size_mb' => round($fileSize / 1024 / 1024, 2),
                                'mime_type' => $file->getMimeType()
                            ]);

                            return true;
                        } catch (\Exception $e) {
                            Log::warning('Error checking file', [
                                'file_name' => $fileName,
                                'error' => $e->getMessage()
                            ]);
                            return false;
                        }
                    });

                    Log::info('Files validation completed', [
                        'question_id' => $question->id,
                        'total_files' => count($files),
                        'valid_files' => count($validFiles),
                        'invalid_files' => count($files) - count($validFiles)
                    ]);

                    if (!empty($validFiles)) {
                        try {
                            Log::info('Starting file upload process', [
                                'question_id' => $question->id,
                                'answer_id' => $answer->id,
                                'valid_files_count' => count($validFiles)
                            ]);

                            $uploadedFiles = $this->uploadAnswerFiles($answer, $validFiles, $question);

                            Log::info('File upload completed', [
                                'question_id' => $question->id,
                                'answer_id' => $answer->id,
                                'uploaded_files_count' => count($uploadedFiles),
                                'uploaded_files' => $uploadedFiles
                            ]);
                        } catch (\Exception $e) {
                            Log::error('Error uploading files for question', [
                                'question_id' => $question->id,
                                'answer_id' => $answer->id,
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);
                            // لا نوقف العملية، نكمل مع باقي الإجابات
                        }
                    } else {
                        Log::warning('No valid files to upload', [
                            'question_id' => $question->id,
                            'answer_id' => $answer->id,
                            'total_files' => count($files)
                        ]);
                    }
                }

                $savedAnswers[] = [
                    'question_id' => $question->id,
                    'answer_id' => $answer->id,
                ];
            }

            // إرسال البريد الإلكتروني مع رابط التأكيد
            if ($request->email) {
                try {
                    $confirmUrl = url('/api/user/offers/confirm/' . $offer->confirm_token);
                    Mail::to($request->email)->send(new OfferCreated($lang, $confirmUrl));
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

            Log::info('=== Transaction Committed ===', [
                'offer_id' => $offer->id,
                'saved_answers_count' => count($savedAnswers),
                'saved_answers' => $savedAnswers
            ]);

            // جلب الـ Offer مع الإجابات
            $offer->load(['answers.question', 'answers.options', 'answers.files']);

            Log::info('=== Final Offer Data ===', [
                'offer_id' => $offer->id,
                'total_answers' => $offer->answers->count(),
                'answers_with_files' => $offer->answers->filter(function ($answer) {
                    return $answer->files->count() > 0;
                })->map(function ($answer) {
                    return [
                        'answer_id' => $answer->id,
                        'question_id' => $answer->question_id,
                        'files_count' => $answer->files->count(),
                        'files' => $answer->files->map(function ($file) {
                            return [
                                'file_id' => $file->id,
                                'file_name' => $file->file_name,
                                'file_path' => $file->file_path,
                                'file_url' => $file->file_url
                            ];
                        })->toArray()
                    ];
                })->values()->toArray()
            ]);

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
            $offer = Offer::with(['type', 'country', 'city'])->find($offer_id);

            if (!$offer || !$offer->type) {
                return;
            }

            // Check if offer has country and city
            if (!$offer->country_id || !$offer->city_id) {
                return; // Skip if offer doesn't have location
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
                ->whereHas('countries', function ($query) use ($offer) {
                    $query->where('country_id', $offer->country_id);
                })
                ->whereHas('cities', function ($query) use ($offer) {
                    $query->where('city_id', $offer->city_id);
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
