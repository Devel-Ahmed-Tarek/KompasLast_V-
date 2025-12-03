<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\QuestionOption;
use App\Models\TypeQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminQuestionOptionController extends Controller
{
    /**
     * عرض جميع اختيارات السؤال
     */
    public function index($question_id)
    {
        $question = TypeQuestion::findOrFail($question_id);
        $options = QuestionOption::where('question_id', $question_id)
            ->orderBy('order')
            ->get()
            ->map(function ($option) {
                // إرجاع الـ URL الكامل للصورة
                if ($option->icon) {
                    $option->icon = asset($option->icon);
                }
                return $option;
            });
        
        return HelperFunc::sendResponse(200, 'Options retrieved successfully', $options);
    }

    /**
     * إنشاء اختيار جديد
     */
    public function store(Request $request, $question_id)
    {
        $validator = Validator::make($request->all(), [
            'option_text' => 'required|array',
            'option_text.en' => 'required|string',
            'option_text.de' => 'nullable|string',
            'option_text.fr' => 'nullable|string',
            'option_text.it' => 'nullable|string',
            'option_text.ar' => 'nullable|string',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->errors());
        }

        $question = TypeQuestion::findOrFail($question_id);
        
        $data = [
            'question_id' => $question_id,
            'option_text' => $request->option_text,
            'order' => $request->order ?? 0,
        ];

        // رفع الصورة/الأيقونة إذا تم إرسالها
        if ($request->hasFile('icon')) {
            $iconPath = HelperFunc::uploadFile('question_options', $request->file('icon'));
            $data['icon'] = $iconPath;
        }

        $option = QuestionOption::create($data);

        // إرجاع الـ URL الكامل للصورة
        if ($option->icon) {
            $option->icon = asset($option->icon);
        }

        return HelperFunc::sendResponse(201, 'Option created successfully', $option);
    }

    /**
     * عرض اختيار محدد
     */
    public function show($question_id, $id)
    {
        $option = QuestionOption::where('question_id', $question_id)
            ->findOrFail($id);
        
        // إرجاع الـ URL الكامل للصورة
        if ($option->icon) {
            $option->icon = asset($option->icon);
        }
        
        return HelperFunc::sendResponse(200, 'Option retrieved successfully', $option);
    }

    /**
     * تحديث اختيار
     */
    public function update(Request $request, $question_id, $id)
    {
        $validator = Validator::make($request->all(), [
            'option_text' => 'nullable|array',
            'option_text.en' => 'nullable|string',
            'option_text.de' => 'nullable|string',
            'option_text.fr' => 'nullable|string',
            'option_text.it' => 'nullable|string',
            'option_text.ar' => 'nullable|string',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->errors());
        }

        $option = QuestionOption::where('question_id', $question_id)->findOrFail($id);
        
        if ($request->has('option_text')) {
            foreach ($request->option_text as $lang => $text) {
                $option->setTranslation('option_text', $lang, $text);
            }
        }

        // رفع صورة جديدة إذا تم إرسالها
        if ($request->hasFile('icon')) {
            // حذف الصورة القديمة إذا كانت موجودة
            if ($option->icon && file_exists(public_path($option->icon))) {
                HelperFunc::deleteFile(public_path($option->icon));
            }
            // رفع الصورة الجديدة
            $iconPath = HelperFunc::uploadFile('question_options', $request->file('icon'));
            $option->icon = $iconPath;
        }
        
        $option->update($request->only(['order']));

        // إرجاع الـ URL الكامل للصورة
        if ($option->icon) {
            $option->icon = asset($option->icon);
        }

        return HelperFunc::sendResponse(200, 'Option updated successfully', $option);
    }

    /**
     * حذف اختيار
     */
    public function destroy($question_id, $id)
    {
        $option = QuestionOption::where('question_id', $question_id)->findOrFail($id);
        
        // حذف الصورة إذا كانت موجودة
        if ($option->icon && file_exists(public_path($option->icon))) {
            HelperFunc::deleteFile(public_path($option->icon));
        }
        
        $option->delete();

        return HelperFunc::sendResponse(200, 'Option deleted successfully', []);
    }
}
