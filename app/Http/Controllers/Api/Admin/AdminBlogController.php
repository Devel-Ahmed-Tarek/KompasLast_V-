<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Models\Blog;
use App\Models\BlogMedia;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AdminBlogController extends BaseController
{
    public function __construct()
    {
        $this->middleware('can:Website Blogs index', ['only' => ['index']]);
        $this->middleware('can:Website Blogs update', ['only' => ['update', 'index', 'show']]);
        $this->middleware('can:Website Blogs store', ['only' => ['store']]);
        $this->middleware('can:Website Blogs show', ['only' => ['show', 'index']]);
        $this->middleware('can:Website Blogs delete', ['only' => ['deleted', 'index']]);
        $this->middleware('can:Website Blogs Update Status', ['only' => ['updateStatus', 'index']]);
    }
    public function index()
    {
        // Fetch paginated blogs
        $blogs = Blog::with(['type', 'media'])->orderBy('id', 'desc')->paginate(10);

        // Map over each blog to add asset URLs for images
        $blogs->getCollection()->transform(function ($blog) {
            $images     = $blog->image;
            $mainImages = $blog->main_image;

            // Convert image paths to full URLs for all languages
            $languages = ['en', 'de', 'fr', 'it', 'ar'];
            foreach ($languages as $lang) {
                $images[$lang]     = isset($images[$lang]) && $images[$lang] ? asset($images[$lang]) : null;
                $mainImages[$lang] = isset($mainImages[$lang]) && $mainImages[$lang] ? asset($mainImages[$lang]) : null;
            }

            $blog->image      = $images;
            $blog->main_image = $mainImages;

            // إضافة الصور الديناميكية
            $blog->dynamic_media = $this->formatDynamicMedia($blog);

            return $blog;
        });

        // Return paginated response with transformed data
        return HelperFunc::pagination($blogs, $blogs->items());
    }

    public function store(Request $request)
    {
        $imageLanguages = ['en', 'de', 'fr', 'it', 'ar']; // Define supported languages

        $validator = Validator::make($request->all(), [
            'type_id'           => 'required|exists:types,id',
            'title'             => 'required|array',
            'main_image'        => 'required|array',
            'main_image.*'      => 'file|image|max:2048',
            'image'             => 'nullable|array',
            'image.*'           => 'file|image|max:2048',
            'short_description' => 'required|array',
            'body'              => 'required|array',
            'key_key'           => 'required|array',
            'meta_title'        => 'required|array',
            'key_description'   => 'required|array',
            'btn'               => 'required|array',
            'slug'              => 'required|array',
            'btn_hrf'           => 'required|string',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation Error', [
                'errors' => $validator->errors(),
            ]);
        }

        $validated = $validator->validated();

        $mainImages = [];
        $images     = [];

        foreach ($imageLanguages as $lang) {
            if (isset($request->main_image[$lang])) {
                $mainImages[$lang] = HelperFunc::uploadFile('/images', $request->main_image[$lang]);
            }

            if (isset($request->image[$lang])) {
                $images[$lang] = HelperFunc::uploadFile('/images', $request->image[$lang]);
            }
        }

        try {
            $id = DB::table('blogs')->insertGetId([
                'type_id'           => $validated['type_id'],
                'title'             => json_encode($validated['title']),
                'slug'              => json_encode($validated['slug']),
                'btn_hrf'           => $validated['btn_hrf'],
                'short_description' => json_encode($validated['short_description']),
                'body'              => json_encode($validated['body']),
                'key_key'           => json_encode($validated['key_key']),
                'meta_title'        => json_encode($validated['meta_title']),
                'key_description'   => json_encode($validated['key_description']),
                'btn'               => json_encode($validated['btn']),
                'image'             => json_encode($images),
                'main_image'        => json_encode($mainImages),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            $post = Blog::find($id);

            // معالجة الصور الديناميكية
            $this->processDynamicMedia($request, $post);

            // إرجاع البيانات مع الصور
            $post->load('media');
            $post->dynamic_media = $this->formatDynamicMedia($post);

            return HelperFunc::sendResponse(200, 'Post created successfully', $post);
        } catch (Exception $e) {
            return HelperFunc::sendResponse(500, 'Server Error', [$e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        $imageLanguages = ['en', 'de', 'fr', 'it', 'ar']; // Supported languages

        // Find the blog post
        $post = Blog::findOrFail($id);

        // Validate the incoming data
        $validator = Validator::make($request->all(), [
            'type_id'           => 'sometimes|exists:types,id',
            'title'             => 'sometimes|array',
            'main_image'        => 'nullable|array',      // Array for multilingual main images
            'main_image.*'      => 'file|image|max:2048', // Validate each main image
            'image'             => 'nullable|array',      // Optional array for small images
            'image.*'           => 'file|image|max:2048', // Validate each small image
            'short_description' => 'sometimes|array',
            'body'              => 'sometimes|array',
            'key_key'           => 'sometimes|array',
            'meta_title'        => 'sometimes|array',
            'key_description'   => 'sometimes|array',
            'btn'               => 'sometimes|array',
            'slug'              => 'sometimes|array', // Expecting array for multilingual data
            'btn_hrf'           => 'sometimes|string',
            'status'            => 'sometimes',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation Error', [
                'errors' => $validator->errors(),
            ]);
        }

        // Retrieve existing translations for images
        $smallImages = $post->image;
        $mainImages  = $post->main_image;

        // Handle image uploads and updates
        foreach ($imageLanguages as $lang) {

            // Update  images
            if ($request->hasFile("image.$lang")) {
                // Delete existing file
                if (isset($mainImages[$lang])) {
                    HelperFunc::deleteFile($smallImages[$lang]);
                }

                // Upload new  image
                $smallImages[$lang] = HelperFunc::uploadFile('/blogs', $request->file("image.$lang"));
            }

            // Update main images
            if ($request->hasFile("main_image.$lang")) {
                // Delete existing file
                if (isset($mainImages[$lang])) {
                    HelperFunc::deleteFile($mainImages[$lang]);
                }

                // Upload new main image
                $mainImages[$lang] = HelperFunc::uploadFile('/blogs', $request->file("main_image.$lang"));
            }
        }

        // Update translations for the blog
        $validated = $request->only([
            'type_id',
            'title',
            'short_description',
            'body',
            'meta_title',
            'key_key',
            'key_description',
            'btn',
            'slug',
            'btn_hrf',
        ]);

        foreach ($validated as $key => $value) {
            if (in_array($key, $post->translatable)) {
                $post->setTranslations($key, $value);
            } else {
                $post->{$key} = $value;
            }
        }

        // Update images
        $post->image      = $smallImages;
        $post->main_image = $mainImages;

        // معالجة الصور الديناميكية
        $this->processDynamicMedia($request, $post);

        // Save the updated post
        $post->save();

        // إرجاع البيانات مع الصور
        $post->load('media');
        $post->dynamic_media = $this->formatDynamicMedia($post);

        return HelperFunc::sendResponse(200, 'Post updated successfully', $post);
    }

    public function deleted($id)
    {
        try {
            // البحث عن المنشور
            $blog = Blog::findOrFail($id);

            $images     = $blog->image;
            $mainImages = $blog->main_image;

            // Convert image paths to full URLs for all languages
            $languages = ['en', 'de', 'fr', 'it', 'ar'];
            foreach ($languages as $lang) {
                if (isset($images[$lang]) && $images[$lang]) {
                    HelperFunc::deleteFile($images[$lang]);
                }
                if (isset($mainImages[$lang]) && $mainImages[$lang]) {
                    HelperFunc::deleteFile($mainImages[$lang]);
                }
            }

            // حذف المنشور
            $blog->delete();

            return HelperFunc::sendResponse(200, 'Blog deleted successfully');
        } catch (ModelNotFoundException $e) {
            return HelperFunc::sendResponse(404, 'Blog not found');
        } catch (Exception $e) {
            return HelperFunc::sendResponse(500, 'An error occurred while deleting the blog');
        }
    }
    public function show($id)
    {
        try {
            // البحث عن المنشور مع العلاقة
            $blog = Blog::with(['type', 'media'])->findOrFail($id);

            // فك تشفير الحقول المخزنة كـ JSON (مثل الصور)
            $originalImages     = $blog->image ?? [];
            $originalMainImages = $blog->main_image ?? [];

            // إضافة الروابط الكاملة للصور
            $languages  = ['en', 'de', 'fr', 'it', 'ar'];
            $images     = [];
            $mainImages = [];
            foreach ($languages as $lang) {
                $mainImages[$lang] = isset($originalMainImages[$lang]) && $originalMainImages[$lang] ? asset($originalMainImages[$lang]) : null;
                $images[$lang]     = isset($originalImages[$lang]) && $originalImages[$lang] ? asset($originalImages[$lang]) : null;
            }

            $blog->image      = $images;
            $blog->main_image = $mainImages;

            // إضافة الصور الديناميكية
            $blog->dynamic_media = $this->formatDynamicMedia($blog);

            // إرجاع التفاصيل بنجاح
            return HelperFunc::sendResponse(200, 'Blog details retrieved successfully', $blog);
        } catch (ModelNotFoundException $e) {
            return HelperFunc::sendResponse(404, 'Blog not found', []);
        } catch (Exception $e) {
            return HelperFunc::sendResponse(500, 'An error occurred while retrieving the blog: ' . $e->getMessage(), []);
        }
    }

    public function updateStatus($id, $status)
    {
        $blog         = Blog::with('type')->findOrFail($id);
        $blog->status = $status;
        $blog->save();
        return HelperFunc::sendResponse(200, 'Post updated successfully', $blog->status);
    }

    /**
     * معالجة الصور الديناميكية - يقبل أي حقل وأي لغة
     */
    private function processDynamicMedia(Request $request, Blog $blog)
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
                    $this->saveMediaFile($blog, $fieldName, $language, $file, 0);
                }
                // إذا كان array من الملفات (أكثر من صورة لنفس الحقل واللغة)
                elseif (is_array($files)) {
                    foreach ($files as $order => $file) {
                        if ($request->hasFile("media.{$fieldName}.{$language}.{$order}")) {
                            $uploadedFile = $request->file("media.{$fieldName}.{$language}.{$order}");
                            $this->saveMediaFile($blog, $fieldName, $language, $uploadedFile, $order);
                        }
                    }
                }
            }
        }
    }

    /**
     * حفظ ملف في جدول blog_media
     */
    private function saveMediaFile(Blog $blog, $fieldName, $language, $file, $order = 0)
    {
        // حذف الصورة القديمة إذا كانت موجودة (نفس الحقل + نفس اللغة + نفس الترتيب)
        BlogMedia::where('blog_id', $blog->id)
            ->where('field_name', $fieldName)
            ->where('language', $language)
            ->where('order', $order)
            ->delete();

        // رفع الملف
        $filePath = HelperFunc::uploadFile('/images', $file);

        // تحديد نوع الملف
        $fileType = $this->getFileType($file);

        // حفظ في قاعدة البيانات
        BlogMedia::create([
            'blog_id' => $blog->id,
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
    private function formatDynamicMedia(Blog $blog)
    {
        $media = $blog->media()->orderBy('field_name')->orderBy('language')->orderBy('order')->get();

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
