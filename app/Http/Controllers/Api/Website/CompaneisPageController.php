<?php
namespace App\Http\Controllers\Api\Website;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Http\Resources\Website\CompaniesPageResource;
use App\Http\Resources\Website\companyPageResource;
use App\Models\PageCompany;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class CompaneisPageController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $language = $request->get('lang', 'en');
        App::setLocale($language);

        $companies = User::where('role', 'company')
            ->where('ban', '0')
            ->whereHas('companyDetails', function ($query) {
                $query->where('sucsses', '1');
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'LIKE', '%' . $search . '%')
                        ->orWhereHas('companyDetails', function ($q) use ($search) {
                            $q->where('about', 'LIKE', '%' . $search . '%');
                        });
                });
            })
            ->with([
                'companyDetails:about,user_id',
                'typesComapny:name',
            ])
            ->select('id', 'img', 'name')
            ->withCount('shopping_list')
            ->orderBy('shopping_list_count', 'desc')
            ->paginate(10);

        $pageData = PageCompany::first();

        return HelperFunc::sendResponse(200, 'done', [
            'companies' => HelperFunc::paginationNew(CompaniesPageResource::collection($companies), $companies),
            'pageData'  => [
                'title'            => $pageData->title,
                'sub_title'        => $pageData->sub_title,
                'description'      => $pageData->description,
                'image'            => $pageData->image ? asset($pageData->image) : null,
                'form_title'       => $pageData->form_title,
                'image_title'      => $pageData->image_title,
                'meta_key'         => $pageData->meta_key,
                'meta_title'       => $pageData->meta_title,
                'meta_description' => $pageData->meta_description,
            ],
        ]);
    }

    public function getCompany(Request $request, $company_id)
    {
        $language = $request->get('lang', 'en');
        App::setLocale($language);

        $company = User::whereHas('companyDetails')
            ->with('companyDetails')
            ->findOrFail($company_id);

        return HelperFunc::apiResponse(true, 200, new companyPageResource($company));
    }

}
