<?php
namespace App\Http\Controllers\Api\Website;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Http\Resources\Website\BlogPageResource;
use App\Http\Resources\Website\BlogsPageResource;
use App\Http\Resources\Website\SingleServesBlogsPageResourcee;
use App\Models\Blog;
use App\Models\BlogsPage;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class BlogPageController extends Controller
{
    public function index(Request $request, $slug)
    {
        $language = $request->get('lang', 'en'); // Default to 'en' if no language is provided
        App::setLocale($language);
        $blog = Blog::with('media')->whereRaw("JSON_SEARCH(slug, 'one', ?) IS NOT NULL", [$slug])
            ->firstOrFail();
        return HelperFunc::sendResponse(200, 'done', new BlogPageResource($blog));
    }

    public function GetPageBlogs(Request $request)
    {
        $language = $request->get('lang', 'en');
        App::setLocale($language);
        $data['page'] = BlogsPage::first();
        // جلب أحدث ثلاث مدونات
        $data['lastBlogPosts'] = Blog::with(['type.typeDitaliServices', 'media'])->where('status', 1)
            ->select('id', 'type_id', 'slug', 'image', 'short_description', 'title')
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        // جلب جميع الخدمات مع المدونات المرتبطة بها
        $data['allServesesBlogs'] = Type::with(['blogs' => function ($query) {
                $query->where('status', 1)
                ->select('id', 'slug', 'type_id', 'image', 'short_description', 'title', 'created_at')
                ->orderBy('created_at', 'desc')
                ->take(6);
        }, 'blogs.media', 'typeDitaliServices'])
            ->get();

        return HelperFunc::sendResponse(200, 'done', new BlogsPageResource((object) $data));
    }

    public function GetBlogByCategory(Request $request, $slug)
    {
        // return "test";
        $language = request()->get('lang', 'en');
        App::setLocale($language);
        $data['page']  = BlogsPage::first();
        $lastBlogPosts = Blog::where('status', 1)
            ->whereRaw("JSON_SEARCH(slug, 'one', ?) IS NOT NULL", [$slug])
            ->select('id', 'slug', 'type_id', 'image', 'short_description', 'title')
            ->orderBy('id', 'desc')
            ->take(3)
            ->get();

        $allServesesBlogs = Type::whereHas('typeDitaliServices', function ($query) use ($slug) {
            $query->whereRaw("JSON_SEARCH(slug, 'one', ?) IS NOT NULL", [$slug]);
        })
            ->with(['blogs' => function ($query) {
                $query->where('status', 1)
                    ->select('id', 'slug', 'type_id', 'image', 'short_description', 'title', 'created_at')
                    ->orderBy('id', 'desc')
                    ->take(6);
            },
                'typeDitaliServices'])
            ->paginate(10);

        return HelperFunc::sendResponse(200, 'done', [
            'allServesesBlogs' => HelperFunc::paginationNew(SingleServesBlogsPageResourcee::collection($allServesesBlogs), $allServesesBlogs),
            'lastBlogPosts'    => $lastBlogPosts->map(function ($blog) {
                return [
                    'id'                => $blog->id,
                    'slug'              => $blog->slug,
                    'image'             => HelperFunc::getLocalizedImage($blog->image) ? asset(HelperFunc::getLocalizedImage($blog->image)) : null, // Return multilingual small images                        'short_description' => $blog->short_description,
                    'short_description' => $blog->short_description,
                    'title'             => $blog->title,
                ];
            }),
        ]);
    }
}
