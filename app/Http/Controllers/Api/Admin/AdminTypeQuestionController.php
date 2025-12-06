<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\Type;
use App\Models\TypeQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminTypeQuestionController extends Controller
{
    /**
     * عرض جميع أسئلة الخدمة
     */
    public function index($type_id)
    {
        $type = Type::findOrFail($type_id);
        $questions = TypeQuestion::where('type_id', $type_id)
            ->with(['options', 'parentQuestion', 'childQuestions'])
            ->orderBy('order')
            ->get();

        return HelperFunc::sendResponse(200, 'Questions retrieved successfully', $questions);
    }

    /**
     * إنشاء سؤال جديد
     */
    public function store(Request $request, $type_id)
    {
        $validator = Validator::make($request->all(), [
            'question_text' => 'required|array',
            'question_text.en' => 'required|string',
            'question_text.de' => 'nullable|string',
            'question_text.fr' => 'nullable|string',
            'question_text.it' => 'nullable|string',
            'question_text.ar' => 'nullable|string',
            'question_type' => 'required|in:text,single_choice,multi_choice,number,date,email,phone',
            'is_required' => 'nullable|boolean',
            'allows_file_upload' => 'nullable|boolean',
            'allowed_file_types' => 'nullable', // يقبل string أو array
            'max_files' => 'nullable|integer|min:1',
            'max_file_size' => 'nullable|integer|min:1', // بالـ MB
            'order' => 'nullable|integer',
            'parent_question_id' => 'nullable|exists:type_questions,id',
            'parent_option_id' => 'nullable|exists:question_options,id',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->errors());
        }

        $type = Type::findOrFail($type_id);

        // تحويل allowed_file_types من array إلى string إذا لزم
        $allowedFileTypes = $request->allowed_file_types;
        if (is_array($allowedFileTypes)) {
            $allowedFileTypes = implode(',', $allowedFileTypes);
        } elseif (empty($allowedFileTypes)) {
            $allowedFileTypes = 'image,video,document';
        }

        $question = TypeQuestion::create([
            'type_id' => $type_id,
            'question_text' => $request->question_text,
            'question_type' => $request->question_type,
            'is_required' => $request->is_required ?? true,
            'allows_file_upload' => $request->allows_file_upload ?? false,
            'allowed_file_types' => $allowedFileTypes,
            'max_files' => $request->max_files ?? 10,
            'max_file_size' => $request->max_file_size ?? 10, // بالـ MB
            'order' => $request->order ?? 0,
            'parent_question_id' => $request->parent_question_id,
            'parent_option_id' => $request->parent_option_id,
        ]);

        return HelperFunc::sendResponse(201, 'Question created successfully', $question);
    }

    /**
     * عرض سؤال محدد
     */
    public function show($type_id, $id)
    {
        $question = TypeQuestion::where('type_id', $type_id)
            ->with(['options', 'parentQuestion', 'childQuestions'])
            ->findOrFail($id);

        return HelperFunc::sendResponse(200, 'Question retrieved successfully', $question);
    }

    /**
     * تحديث سؤال
     */
    public function update(Request $request, $type_id, $id)
    {
        $validator = Validator::make($request->all(), [
            'question_text' => 'nullable|array',
            'question_text.en' => 'nullable|string',
            'question_text.de' => 'nullable|string',
            'question_text.fr' => 'nullable|string',
            'question_text.it' => 'nullable|string',
            'question_text.ar' => 'nullable|string',
            'question_type' => 'nullable|in:text,single_choice,multi_choice,number,date,email,phone',
            'is_required' => 'nullable|boolean',
            'allows_file_upload' => 'nullable|boolean',
            'allowed_file_types' => 'nullable', // يقبل string أو array
            'max_files' => 'nullable|integer|min:1',
            'max_file_size' => 'nullable|integer|min:1',
            'order' => 'nullable|integer',
            'parent_question_id' => 'nullable|exists:type_questions,id',
            'parent_option_id' => 'nullable|exists:question_options,id',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->errors());
        }

        $question = TypeQuestion::where('type_id', $type_id)->findOrFail($id);

        if ($request->has('question_text')) {
            foreach ($request->question_text as $lang => $text) {
                $question->setTranslation('question_text', $lang, $text);
            }
        }

        // تحويل allowed_file_types من array إلى string إذا لزم
        $updateData = $request->only([
            'question_type',
            'is_required',
            'allows_file_upload',
            'max_files',
            'max_file_size',
            'order',
            'parent_question_id',
            'parent_option_id',
        ]);

        if ($request->has('allowed_file_types')) {
            $allowedFileTypes = $request->allowed_file_types;
            if (is_array($allowedFileTypes)) {
                $updateData['allowed_file_types'] = implode(',', $allowedFileTypes);
            } else {
                $updateData['allowed_file_types'] = $allowedFileTypes;
            }
        }

        $question->update($updateData);

        return HelperFunc::sendResponse(200, 'Question updated successfully', $question);
    }

    /**
     * حذف سؤال
     */
    public function destroy($type_id, $id)
    {
        $question = TypeQuestion::where('type_id', $type_id)->findOrFail($id);
        $question->delete();

        return HelperFunc::sendResponse(200, 'Question deleted successfully', []);
    }

    /**
     * إعادة ترتيب الأسئلة
     */
    public function reorder(Request $request, $type_id, $id)
    {
        $validator = Validator::make($request->all(), [
            'order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->errors());
        }

        $question = TypeQuestion::where('type_id', $type_id)->findOrFail($id);
        $question->order = $request->order;
        $question->save();

        return HelperFunc::sendResponse(200, 'Question order updated successfully', $question);
    }

    /**
     * عرض الـ Flow Tree (شجرة الأسئلة)
     */
    public function getFlowTree($type_id)
    {
        $type = Type::findOrFail($type_id);

        // جلب الأسئلة الرئيسية فقط
        $mainQuestions = TypeQuestion::where('type_id', $type_id)
            ->whereNull('parent_question_id')
            ->with(['options', 'childQuestions.options'])
            ->orderBy('order')
            ->get();

        // بناء الشجرة
        $tree = $mainQuestions->map(function ($question) {
            return $this->buildQuestionTree($question);
        });

        return HelperFunc::sendResponse(200, 'Flow tree retrieved successfully', $tree);
    }

    /**
     * بناء شجرة السؤال (recursive)
     */
    private function buildQuestionTree($question)
    {
        // الحصول على جميع الترجمات لضمان إرجاع جميع اللغات الخمس
        $questionText = $this->getAllLanguages($question, 'question_text');

        $data = [
            'id' => $question->id,
            'question_text' => $questionText,
            'question_type' => $question->question_type,
            'is_required' => $question->is_required,
            'order' => $question->order,
            'parent_question_id' => $question->parent_question_id,
            'parent_option_id' => $question->parent_option_id,
            'options' => $question->options->map(function ($option) {
                $optionText = $this->getAllLanguages($option, 'option_text');
                return [
                    'id' => $option->id,
                    'option_text' => $optionText,
                    'icon' => $option->icon ? asset($option->icon) : null,
                    'order' => $option->order,
                ];
            }),
            'child_questions' => [],
        ];

        // جلب الأسئلة الفرعية
        $childQuestions = TypeQuestion::where('parent_question_id', $question->id)
            ->with(['options', 'childQuestions'])
            ->orderBy('order')
            ->get();

        foreach ($childQuestions as $child) {
            $childData = $this->buildQuestionTree($child);
            // إضافة معلومات الـ parent option للتوضيح
            if ($child->parent_option_id) {
                $parentOption = \App\Models\QuestionOption::find($child->parent_option_id);
                if ($parentOption) {
                    $parentOptionText = $this->getAllLanguages($parentOption, 'option_text');
                    $childData['triggered_by_option'] = [
                        'id' => $parentOption->id,
                        'option_text' => $parentOptionText,
                        'icon' => $parentOption->icon ? asset($parentOption->icon) : null,
                    ];
                }
            }
            $data['child_questions'][] = $childData;
        }

        return $data;
    }

    /**
     * الحصول على جميع اللغات الخمس للحقل المترجم
     */
    private function getAllLanguages($model, $field)
    {
        $languages = ['en', 'de', 'fr', 'it', 'ar'];

        // محاولة استخدام getTranslations أولاً (Spatie Translatable)
        try {
            $translations = $model->getTranslations($field);
            if (is_array($translations) && !empty($translations)) {
                $result = [];
                foreach ($languages as $lang) {
                    $result[$lang] = $translations[$lang] ?? null;
                }
                return $result;
            }
        } catch (\Exception $e) {
            // إذا فشل getTranslations، نتابع للطرق الأخرى
        }

        // محاولة الوصول المباشر للحقل (قد يكون JSON object)
        $value = $model->$field;

        if (is_array($value)) {
            $result = [];
            foreach ($languages as $lang) {
                $result[$lang] = $value[$lang] ?? null;
            }
            return $result;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $result = [];
                foreach ($languages as $lang) {
                    $result[$lang] = $decoded[$lang] ?? null;
                }
                return $result;
            }
        }

        // إذا لم نجد أي ترجمات، نعيد array بجميع اللغات كـ null
        $result = [];
        foreach ($languages as $lang) {
            $result[$lang] = null;
        }
        return $result;
    }
}
