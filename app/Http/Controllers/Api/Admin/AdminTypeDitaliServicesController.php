<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\Type;
use App\Models\TypeDitaliServices;
use App\Models\TypeServiceMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AdminTypeDitaliServicesController extends Controller
{

    private $languages = ['en', 'de', 'fr', 'it', 'ar'];

    public function show($id)
    {
        $date = TypeDitaliServices::where('type_id', $id)->with(['type', 'media'])->first();
        if ($date == []) {
            return HelperFunc::sendResponse(200, 'done', $date);
        }

        // معالجة الصور القديمة (للتوافق)
        if ($date->small_image != []) {
            $date->small_image = [
                'en' => $date->small_image['en'] ? asset($date->small_image['en']) : null,
                'de' => $date->small_image['de'] ? asset($date->small_image['de']) : null,
                'fr' => $date->small_image['fr'] ? asset($date->small_image['fr']) : null,
                'it' => $date->small_image['it'] ? asset($date->small_image['it']) : null,
                'ar' => $date->small_image['ar'] ?? null ? asset($date->small_image['ar']) : null,
            ];
        }
        if ($date->main_image != []) {
            $date->main_image = [
                'en' => $date->main_image['en'] ? asset($date->main_image['en']) : null,
                'de' => $date->main_image['de'] ? asset($date->main_image['de']) : null,
                'fr' => $date->main_image['fr'] ? asset($date->main_image['fr']) : null,
                'it' => $date->main_image['it'] ? asset($date->main_image['it']) : null,
                'ar' => $date->main_image['ar'] ?? null ? asset($date->main_image['ar']) : null,
            ];
        }
        if ($date->service_home_icon) {
            $date->service_home_icon = asset($date->service_home_icon);
        }

        // إضافة الصور الديناميكية
        $date->dynamic_media = $this->formatDynamicMedia($date);

        return HelperFunc::sendResponse(200, 'done', $date);
    }

    /**
     * تنسيق الصور الديناميكية للعرض
     */
    private function formatDynamicMedia(TypeDitaliServices $service)
    {
        $media = $service->media()->orderBy('field_name')->orderBy('language')->orderBy('order')->get();

        $formatted = [];

        foreach ($media as $item) {
            if (!isset($formatted[$item->field_name])) {
                $formatted[$item->field_name] = [];
            }

            if (!isset($formatted[$item->field_name][$item->language])) {
                $formatted[$item->field_name][$item->language] = [];
            }

            $formatted[$item->field_name][$item->language][] = [
                'id' => $item->id,
                'file_path' => asset($item->file_path),
                'file_name' => $item->file_name,
                'file_type' => $item->file_type,
                'file_size' => $item->file_size,
                'order' => $item->order,
                'metadata' => $item->metadata,
            ];
        }

        return $formatted;
    }

    public function store(Request $request)
    {
        // التحقق من البيانات
        $validator = $this->validateData($request);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->errors()->all());
        }

        try {
            $validated = $validator->validated();
            $nameType  = Type::find($validated['type_id'])->name;
            if ($request->hasFile('service_home_icon')) {
                $service_home_icon = HelperFunc::uploadFile('/images', $request->file('service_home_icon'));
            }
            $service = TypeDitaliServices::create([
                'type_id'           => $validated['type_id'],
                'small_image'       => [
                    'en' => HelperFunc::uploadFile('/images', $request->small_image['en']),
                    'de' => HelperFunc::uploadFile('/images', $request->small_image['de']),
                    'it' => HelperFunc::uploadFile('/images', $request->small_image['it']),
                    'fr' => HelperFunc::uploadFile('/images', $request->small_image['fr']),
                ],
                'main_image'        => [
                    'en' => HelperFunc::uploadFile('/images', $request->main_image['en']),
                    'de' => HelperFunc::uploadFile('/images', $request->main_image['de']),
                    'it' => HelperFunc::uploadFile('/images', $request->main_image['it']),
                    'fr' => HelperFunc::uploadFile('/images', $request->main_image['fr']),
                ],

                'short_description' => $validated['short_description'],
                'service_home_icon' => $service_home_icon,
                'feature_header'    => $validated['feature_header'],
                'feature_sub_title' => $validated['feature_sub_title'],
                'body'              => $validated['body'],
                'tips_title'        => $validated['tips_title'],
                'tips_subtitle'     => $validated['tips_subtitle'],
                'meta_keys'         => $validated['meta_keys'],
                'meta_title'        => $validated['meta_title'],
                'meta_description'  => $validated['meta_description'],
                'slug'              => $nameType,
            ]);

            return HelperFunc::sendResponse(201, 'Service created successfully', $service);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while processing the request.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    private function validateData(Request $request)
    {
        $rules = [
            'type_id'           => 'required',
            'service_home_icon' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:10048',
        ];

        foreach ($this->languages as $lang) {
            $rules["small_image.$lang"]       = 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048';
            $rules["main_image.$lang"]        = 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048';
            $rules["short_description.$lang"] = 'required|string';
            $rules["feature_header.$lang"]    = 'required|string';
            $rules["feature_sub_title.$lang"] = 'required|string';
            $rules["body.$lang"]              = 'required|string';
            $rules["tips_title.$lang"]        = 'required|string';
            $rules["tips_subtitle.$lang"]     = 'required|string';
            $rules["meta_keys.$lang"]         = 'required|string';
            $rules["meta_description.$lang"]  = 'required|string';
        }

        return Validator::make($request->all(), $rules); // إرجاع Validator بدلاً من البيانات
    }

    public function update(Request $request, $id)
    {
        try {
            // البحث عن الخدمة بواسطة الـ ID
            $service = TypeDitaliServices::findOrFail($id);

            // التحقق من صحة البيانات المرسلة
            $validator = Validator::make($request->all(), [
                'short_description'       => 'nullable|array',
                'short_description.*'     => 'nullable|string',
                'service_home_icon'       => 'nullable|file|mimes:jpeg,png,jpg,gif|max:10048',
                'feature_header.*'        => 'nullable|string',
                'feature_header'          => 'nullable|array',
                'feature_sub_title.*'     => 'nullable|string',
                'feature_sub_title'       => 'nullable|array',
                'body'                    => 'nullable|array',
                'body.*'                  => 'nullable|string',
                'tips_title'              => 'nullable|array',
                'tips_title.*'            => 'nullable|string',
                'tips_subtitle'           => 'nullable|array',
                'tips_subtitle.*'         => 'nullable|string',
                'meta_keys'               => 'nullable|array',
                'meta_keys.*'             => 'nullable|string',
                'meta_title'              => 'nullable|array',
                'meta_title.*'            => 'nullable|string',
                'blog_meta_title'         => 'nullable|array',
                'blog_meta_title.*'       => 'nullable|string',
                'blog_meta_keys'          => 'nullable|array',
                'blog_meta_keys.*'        => 'nullable|string',
                'slug'                    => 'nullable|array',
                'slug.*'                  => 'nullable|string|max:1000',
                'meta_description'        => 'nullable|array',
                'meta_description.*'      => 'nullable|string|max:500',
                'blog_meta_description'   => 'nullable|array',
                'blog_meta_description.*' => 'nullable|string|max:500',
                'small_image'             => 'nullable|array',
                'small_image.*'           => 'nullable|file|mimes:jpeg,png,jpg,gif|max:10048',
                'main_image'              => 'nullable|array',
                'main_image.*'            => 'nullable|file|mimes:jpeg,png,jpg,gif|max:10048',
            ]);

            if ($validator->fails()) {
                return HelperFunc::sendResponse(422, 'Validation errors', $validator->errors());
            }

            // الحصول على البيانات المصدقة
            $validated = $validator->validated();

            // معالجة الصور الديناميكية (النظام الجديد)
            $this->processDynamicMedia($request, $service);

            // معالجة الصور القديمة (للتوافق مع النظام القديم)
            $languages = ['en', 'de', 'it', 'fr', 'ar'];

            // دالة لمعالجة الصور القديمة
            $processImages = function ($type) use ($request, $service, $languages) {
                $images = [];
                foreach ($languages as $lang) {
                    if ($request->hasFile("{$type}.{$lang}")) {
                        $images[$lang] = HelperFunc::uploadFile('/images', $request->file("{$type}.{$lang}"));
                    } else {
                        // الاحتفاظ بالصورة القديمة إذا لم يتم تحديثها
                        $images[$lang] = $service->{$type}[$lang] ?? null;
                    }
                }
                return $images;
            };

            if ($request->hasFile('service_home_icon')) {
                $service->service_home_icon = HelperFunc::uploadFile('/images', $request->file('service_home_icon'));
            }
            // تحديث الصور الصغيرة والرئيسية (للتوافق مع النظام القديم)
            if ($request->has('small_image') || $request->hasFile('small_image')) {
                $service->small_image = $processImages('small_image');
            }
            if ($request->has('main_image') || $request->hasFile('main_image')) {
                $service->main_image = $processImages('main_image');
            }

            // تحديث الحقول النصية (التي تدعم الترجمة)
            foreach ($service->getTranslatableAttributes() as $field) {
                if (isset($validated[$field])) {
                    foreach ($validated[$field] as $locale => $value) {
                        $service->setTranslation($field, $locale, $value);
                    }
                }
            }

            // حفظ التغييرات في قاعدة البيانات
            $service->save();

            return HelperFunc::sendResponse(200, 'Service updated successfully', $service);
        } catch (\Exception $e) {
            Log::error('Update Service Error: ' . $e->getMessage());
            return HelperFunc::sendResponse(500, 'An error occurred while updating the service.', [$e->getMessage()]);
        }
    }

    /**
     * معالجة الصور الديناميكية - يقبل أي حقل وأي لغة
     * 
     * مثال على البيانات المرسلة:
     * media[small_image][en][0] = file
     * media[small_image][en][1] = file (صورة ثانية لنفس الحقل واللغة)
     * media[main_image][de] = file
     * media[feature_image][fr] = file
     * 
     * أو بشكل أبسط:
     * media[field_name][language] = file
     */
    private function processDynamicMedia(Request $request, TypeDitaliServices $service)
    {
        if (!$request->has('media')) {
            return;
        }

        $mediaData = $request->input('media', []);

        // معالجة كل حقل
        foreach ($mediaData as $fieldName => $languages) {
            if (!is_array($languages)) {
                continue;
            }

            // معالجة كل لغة
            foreach ($languages as $language => $files) {
                // التحقق من أن اللغة صحيحة
                if (!in_array($language, $this->languages)) {
                    continue;
                }

                // إذا كان ملف واحد فقط (ليس array)
                if ($request->hasFile("media.{$fieldName}.{$language}")) {
                    $file = $request->file("media.{$fieldName}.{$language}");
                    $this->saveMediaFile($service, $fieldName, $language, $file, 0);
                }
                // إذا كان array من الملفات (أكثر من صورة لنفس الحقل واللغة)
                elseif (is_array($files)) {
                    foreach ($files as $order => $file) {
                        if ($request->hasFile("media.{$fieldName}.{$language}.{$order}")) {
                            $uploadedFile = $request->file("media.{$fieldName}.{$language}.{$order}");
                            $this->saveMediaFile($service, $fieldName, $language, $uploadedFile, $order);
                        }
                    }
                }
            }
        }
    }

    /**
     * حفظ ملف في جدول type_service_media
     */
    private function saveMediaFile(TypeDitaliServices $service, $fieldName, $language, $file, $order = 0)
    {
        // حذف الصورة القديمة إذا كانت موجودة (نفس الحقل + نفس اللغة + نفس الترتيب)
        TypeServiceMedia::where('type_ditali_service_id', $service->id)
            ->where('field_name', $fieldName)
            ->where('language', $language)
            ->where('order', $order)
            ->delete();

        // رفع الملف
        $filePath = HelperFunc::uploadFile('/images', $file);

        // تحديد نوع الملف
        $fileType = $this->getFileType($file);

        // حفظ في قاعدة البيانات
        TypeServiceMedia::create([
            'type_ditali_service_id' => $service->id,
            'field_name' => $fieldName,
            'language' => $language,
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $fileType,
            'file_size' => round($file->getSize() / 1024), // بالـ KB
            'order' => $order,
            'metadata' => null, // يمكن إضافة metadata لاحقاً
        ]);
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
