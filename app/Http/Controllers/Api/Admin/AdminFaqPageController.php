<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\FaqPage;
use App\Models\FaqPageMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AdminFaqPageController extends Controller
{
    public function index()
    {
        // Fetch the first FAQ page
        $data = FaqPage::with('media')->first();

        if (! $data) {
            return HelperFunc::sendResponse(404, 'FAQ Page not found');
        }

        $images = [];

        // Decode hero_image JSON (للتوافق مع النظام القديم)
        $heroImages = json_decode($data->hero_image, true) ?? [];

        // إضافة اللغة العربية
        $languages = ['en', 'de', 'fr', 'it', 'ar'];
        foreach ($languages as $lang) {
            $images[$lang] = isset($heroImages[$lang]) && $heroImages[$lang] ? asset($heroImages[$lang]) : null;
        }

        $data->hero_image = $images;

        // إضافة الصور الديناميكية
        $data->dynamic_media = $this->formatDynamicMedia($data);

        return HelperFunc::sendResponse(200, 'done', $data);
    }

    public function update(Request $request, $id)
    {
        try {
            $imageLanguages = ['en', 'de', 'fr', 'it', 'ar']; // Define supported languages for images

            // Find the FAQ page
            $post = FaqPage::findOrFail($id);

            // Validate the incoming data
            $validator = Validator::make($request->all(), [
                'title'            => 'sometimes|array',
                'sub_title'        => 'sometimes|array',
                'form_title'       => 'sometimes|array',
                'form_sub_title'   => 'sometimes|array',
                'meta_key'         => 'sometimes|array',
                'meta_title'       => 'sometimes|array',
                'meta_description' => 'sometimes|array',
                'hero_image'       => 'nullable|array',      // Multilingual hero images (للتوافق مع النظام القديم)
                'hero_image.*'     => 'file|image|max:2048', // Validate hero images
            ]);

            if ($validator->fails()) {
                return HelperFunc::sendResponse(422, 'Validation Error', [
                    'errors' => $validator->errors(),
                ]);
            }

            // Retrieve existing hero image data (للتوافق مع النظام القديم)
            $heroImages = json_decode($post->hero_image, true) ?: [];

            // Process updated hero image uploads (للتوافق مع النظام القديم)
            foreach ($imageLanguages as $lang) {
                if ($request->hasFile("hero_image.$lang")) {
                    // Delete existing file (optional)
                    if (isset($heroImages[$lang])) {
                        HelperFunc::deleteFile($heroImages[$lang]);
                    }

                    // Upload new hero image
                    $heroImages[$lang] = HelperFunc::uploadFile('faq', $request->file("hero_image.$lang"));
                }
            }

            // معالجة الصور الديناميكية (النظام الجديد)
            $this->processDynamicMedia($request, $post);

            // Prepare updated data
            $validated = $request->only([
                'title',
                'sub_title',
                'form_title',
                'form_sub_title',
                'meta_key',
                'meta_title',
                'meta_description',
            ]);

            // تحديث الحقول النصية (التي تدعم الترجمة)
            foreach ($post->getTranslatableAttributes() as $field) {
                if (isset($validated[$field])) {
                    foreach ($validated[$field] as $locale => $value) {
                        $post->setTranslation($field, $locale, $value);
                    }
                }
            }

            // تحديث hero_image (للتوافق مع النظام القديم)
            $post->hero_image = $heroImages;

            // Save the updated model
            $post->save();

            // إرجاع البيانات مع الصور
            $post->load('media');
            $post->dynamic_media = $this->formatDynamicMedia($post);

            return HelperFunc::sendResponse(200, 'FAQ Page updated successfully', $post);
        } catch (\Exception $e) {
            Log::error('Update FAQ Page Error: ' . $e->getMessage());
            return HelperFunc::sendResponse(500, 'An error occurred while updating the FAQ page.', [$e->getMessage()]);
        }
    }

    /**
     * معالجة الصور الديناميكية - يقبل أي حقل وأي لغة
     */
    private function processDynamicMedia(Request $request, FaqPage $faqPage)
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
                    $this->saveMediaFile($faqPage, $fieldName, $language, $file, 0);
                }
                // إذا كان array من الملفات (أكثر من صورة لنفس الحقل واللغة)
                elseif (is_array($files)) {
                    foreach ($files as $order => $file) {
                        if ($request->hasFile("media.{$fieldName}.{$language}.{$order}")) {
                            $uploadedFile = $request->file("media.{$fieldName}.{$language}.{$order}");
                            $this->saveMediaFile($faqPage, $fieldName, $language, $uploadedFile, $order);
                        }
                    }
                }
            }
        }
    }

    /**
     * حفظ ملف في جدول faq_page_media
     */
    private function saveMediaFile(FaqPage $faqPage, $fieldName, $language, $file, $order = 0)
    {
        // حذف الصورة القديمة إذا كانت موجودة (نفس الحقل + نفس اللغة + نفس الترتيب)
        FaqPageMedia::where('faq_page_id', $faqPage->id)
            ->where('field_name', $fieldName)
            ->where('language', $language)
            ->where('order', $order)
            ->delete();

        // رفع الملف
        $filePath = HelperFunc::uploadFile('/faq', $file);

        // تحديد نوع الملف
        $fileType = $this->getFileType($file);

        // حفظ في قاعدة البيانات
        FaqPageMedia::create([
            'faq_page_id' => $faqPage->id,
            'field_name' => $fieldName,
            'language' => $language,
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $fileType,
            'file_size' => round($file->getSize() / 1024), // بالـ KB
            'order' => $order,
            'metadata' => null,
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

    /**
     * تنسيق الصور الديناميكية للعرض
     */
    private function formatDynamicMedia(FaqPage $faqPage)
    {
        $media = $faqPage->media()->orderBy('field_name')->orderBy('language')->orderBy('order')->get();

        if ($media->isEmpty()) {
            return null; // إذا مفيش صور، نرجع null
        }

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

        // إذا كان في صورة واحدة فقط لكل حقل و لغة، نرجعها مباشرة
        foreach ($formatted as $fieldName => $languages) {
            foreach ($languages as $language => $images) {
                if (count($images) === 1) {
                    $formatted[$fieldName][$language] = $images[0];
                }
            }
        }

        return $formatted;
    }
}
