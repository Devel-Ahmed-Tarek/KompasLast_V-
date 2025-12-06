<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\TypeTips;
use App\Models\TypeTipsMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminTypeTipsController extends Controller
{
    public function index(Request $request)
    {
        $data = TypeTips::where('type_id', $request->type_id)
            ->with('media')
            ->get();
        return HelperFunc::sendResponse(200, 'done', $data);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type_id' => 'required|exists:types,id',
            'name' => 'required|array', // التأكد من أن الاسم متعدد اللغات
            'name.en' => 'required|string',
            'name.de' => 'nullable|string',
            'name.fr' => 'nullable|string',
            'name.it' => 'nullable|string',
            'name.ar' => 'nullable|string',
        ]);

        $typeTip = TypeTips::create([
            'type_id' => $data['type_id'],
            'name' => $data['name'],
        ]);

        // معالجة الصور الديناميكية
        $this->processDynamicMedia($request, $typeTip);

        // إرجاع البيانات مع الصور
        $typeTip->load('media');

        return HelperFunc::sendResponse(201, 'Type Tip created successfully', $typeTip);
    }
    public function update(Request $request, $id)
    {
        try {
            $data = $request->validate([
                'name' => 'sometimes|array', // قبول البيانات إذا أُرسلت كـ JSON
                'name.en' => 'sometimes|string', // تحديث الإنجليزية إذا تم إرسالها
                'name.de' => 'sometimes|string', // تحديث الألمانية إذا تم إرسالها
                'name.fr' => 'sometimes|string', // تحديث الفرنسية إذا تم إرسالها
                'name.it' => 'sometimes|string', // تحديث الإيطالية إذا تم إرسالها
                'name.ar' => 'sometimes|string', // تحديث العربية
            ]);

            $typeTip = TypeTips::findOrFail($id);

            // التحقق من وجود كل لغة وتحديثها إذا أُرسلت
            if (isset($data['name']['en'])) {
                $typeTip->setTranslation('name', 'en', $data['name']['en']);
            }
            if (isset($data['name']['de'])) {
                $typeTip->setTranslation('name', 'de', $data['name']['de']);
            }
            if (isset($data['name']['fr'])) {
                $typeTip->setTranslation('name', 'fr', $data['name']['fr']);
            }
            if (isset($data['name']['it'])) {
                $typeTip->setTranslation('name', 'it', $data['name']['it']);
            }
            if (isset($data['name']['ar'])) {
                $typeTip->setTranslation('name', 'ar', $data['name']['ar']);
            }

            // معالجة الصور الديناميكية
            $this->processDynamicMedia($request, $typeTip);

            // حفظ التحديثات
            $typeTip->save();

            // إرجاع البيانات مع الصور
            $typeTip->load('media');

            return HelperFunc::sendResponse(200, 'Type Tip updated successfully', $typeTip);
        } catch (\Exception $e) {
            Log::error('Update Type Tip Error: ' . $e->getMessage());
            return HelperFunc::sendResponse(500, 'An error occurred while updating the tip.', [$e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $typeTip = TypeTips::findOrFail($id);

        $typeTip->delete();

        return response()->json([
            'message' => 'Type Tip deleted successfully',
        ], 200);
    }

    /**
     * معالجة الصور الديناميكية - يقبل أي حقل وأي لغة
     * 
     * مثال على البيانات المرسلة:
     * media[icon][en] = file
     * media[image][de] = file
     * media[gallery][fr][0] = file1
     * media[gallery][fr][1] = file2
     */
    private function processDynamicMedia(Request $request, TypeTips $typeTip)
    {
        if (!$request->has('media')) {
            return;
        }

        $mediaData = $request->input('media', []);
        $languages = ['en', 'de', 'fr', 'it', 'ar'];

        // معالجة كل حقل
        foreach ($mediaData as $fieldName => $languagesData) {
            if (!is_array($languagesData)) {
                continue;
            }

            // معالجة كل لغة
            foreach ($languagesData as $language => $files) {
                // التحقق من أن اللغة صحيحة
                if (!in_array($language, $languages)) {
                    continue;
                }

                // إذا كان ملف واحد فقط (ليس array)
                if ($request->hasFile("media.{$fieldName}.{$language}")) {
                    $file = $request->file("media.{$fieldName}.{$language}");
                    $this->saveMediaFile($typeTip, $fieldName, $language, $file, 0);
                }
                // إذا كان array من الملفات (أكثر من صورة لنفس الحقل واللغة)
                elseif (is_array($files)) {
                    foreach ($files as $order => $file) {
                        if ($request->hasFile("media.{$fieldName}.{$language}.{$order}")) {
                            $uploadedFile = $request->file("media.{$fieldName}.{$language}.{$order}");
                            $this->saveMediaFile($typeTip, $fieldName, $language, $uploadedFile, $order);
                        }
                    }
                }
            }
        }
    }

    /**
     * حفظ ملف في جدول type_tips_media
     */
    private function saveMediaFile(TypeTips $typeTip, $fieldName, $language, $file, $order = 0)
    {
        // حذف الصورة القديمة إذا كانت موجودة (نفس الحقل + نفس اللغة + نفس الترتيب)
        TypeTipsMedia::where('type_tip_id', $typeTip->id)
            ->where('field_name', $fieldName)
            ->where('language', $language)
            ->where('order', $order)
            ->delete();

        // رفع الملف
        $filePath = HelperFunc::uploadFile('/images', $file);

        // تحديد نوع الملف
        $fileType = $this->getFileType($file);

        // حفظ في قاعدة البيانات
        TypeTipsMedia::create([
            'type_tip_id' => $typeTip->id,
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
