<?php
namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Models\Blog;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
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
        $blogs = Blog::with('type')->orderBy('id', 'desc')->paginate(10);

        // Map over each blog to add asset URLs for images
        $blogs->getCollection()->transform(function ($blog) {
            $images     = $blog->image;
            $mainImages = $blog->main_image;

            // Convert image paths to full URLs for all languages
            foreach (['en', 'de', 'fr', 'it'] as $lang) {
                $images[$lang]     = isset($images[$lang]) ? asset($images[$lang]) : null;
                $mainImages[$lang] = isset($mainImages[$lang]) ? asset($mainImages[$lang]) : null;
            }

            $blog->image      = $images;
            $blog->main_image = $mainImages;

            return $blog;
        });

        // Return paginated response with transformed data
        return HelperFunc::pagination($blogs, $blogs->items());
    }

    public function store(Request $request)
    {
        $imageLanguages = ['en', 'de', 'fr', 'it']; // Define supported languages

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

            $post = DB::table('blogs')->where('id', $id)->first();

            return HelperFunc::sendResponse(200, 'Post created successfully', $post);

        } catch (Exception $e) {
            return HelperFunc::sendResponse(500, 'Server Error', [$e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        $imageLanguages = ['en', 'de', 'fr', 'it']; // Supported languages

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
            'type_id', 'title', 'short_description', 'body', 'meta_title',
            'key_key', 'key_description', 'btn', 'slug', 'btn_hrf',
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

        // Save the updated post
        $post->save();

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
            foreach (['en', 'de', 'fr', 'it'] as $lang) {
                HelperFunc::deleteFile($images[$lang]);
                HelperFunc::deleteFile($mainImages[$lang]);
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
            $blog = Blog::with('type')->findOrFail($id);

            // فك تشفير الحقول المخزنة كـ JSON (مثل الصور)
            $images     = $blog->image;
            $mainImages = $blog->main_image;

            // إضافة الروابط الكاملة للصور
            $languages  = ['en', 'de', 'fr', 'it'];
            $images     = [];
            $mainImages = [];
            foreach ($languages as $lang) {
                $mainImages[$lang] = isset($blog->main_image[$lang]) ? asset($blog->main_image[$lang]) : null;
                $images[$lang]     = isset($blog->image[$lang]) ? asset($blog->image[$lang]) : null;
            }

            $blog->image      = $images;
            $blog->main_image = $mainImages;

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

}
