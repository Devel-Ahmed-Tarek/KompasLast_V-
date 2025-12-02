<?php
namespace App\Http\Controllers\Api\Website;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Type;
use App\Models\User;

class SitemapController extends Controller
{
    public function index()
    {

        $data['blogs']   = Blog::with('type.typeDitaliServices:slug,id,type_id')->select('id', 'created_at', 'slug', 'type_id')->get();
        $data['servess'] = Type::with(['typeDitaliServices:id,slug,type_id', 'typeDitaliServesPageForm:id,slug,type_id'])
            ->select('id', 'created_at')->get();
        $data['companies'] = User::where('role', 'company')
            ->where('ban', '0')
            ->whereHas('companyDetails', function ($q) {
                $q->where('sucsses', '1');
            })
            ->select('id', 'name', 'created_at')
            ->get();

        return HelperFunc::sendResponse(200, 'Success', $data);
    }
}
