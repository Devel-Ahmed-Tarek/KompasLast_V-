<?php
namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Models\AboutUs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class AdminAboutUsPageController extends BaseController
{
    public function __construct()
    {
        $this->middleware('can:Website About Page Show', ['only' => ['index']]);
        $this->middleware('can:Website About Page update', ['only' => ['update', 'index']]);

    }

    public function index()
    {
        $data              = AboutUs::first();
        $data->hero_image  = asset($data->hero_image);
        $data->target_imge = asset($data->target_imge);
        $data->why_imge3   = asset($data->why_imge3);
        $data->why_imge2   = asset($data->why_imge2);
        $data->why_imge    = asset($data->why_imge);
        return HelperFunc::sendResponse(200, 'done', $data);
    }

    /**
     * Update About Us data.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        // Find the AboutUs record by ID
        $aboutUs = AboutUs::first();

        if (! $aboutUs) {
            return response()->json([
                'message' => 'About Us record not found.',
            ], 404);
        }

        // Validate the incoming request data
        $validated = $request->validate([
            'title'                           => 'nullable|array',
            'hero_title'                      => 'nullable|array',
            'hero_decsription'                => 'nullable|array',
            'hero_image'                      => 'nullable|image|max:2048', // Image validation
            'why_title'                       => 'nullable|array',
            'why_sub_title'                   => 'nullable|array',
            'why_name'                        => 'nullable|array',
            'why_name2'                       => 'nullable|array',
            'why_name3'                       => 'nullable|array',
            'why_imge'                        => 'nullable|image|max:2048',
            'why_imge2'                       => 'nullable|image|max:2048',
            'why_imge3'                       => 'nullable|image|max:2048',
            'why_decsription'                 => 'nullable|array',
            'why_decsription2'                => 'nullable|array',
            'why_decsription3'                => 'nullable|array',
            'target_title'                    => 'nullable|array',
            'target_imge'                     => 'nullable|image|max:2048',
            'target_body'                     => 'nullable|array',
            'informaion_customer_cont'        => 'nullable',
            'informaion_company_cont'         => 'nullable',
            'informaion_offer_cont'           => 'nullable',
            'informaion_company_decsription'  => 'nullable|array',
            'informaion_offer_decsription'    => 'nullable|array',
            'informaion_customer_decsription' => 'nullable|array',
            'informaion_company_name'         => 'nullable|array',
            'informaion_offer_name'           => 'nullable|array',
            'informaion_customer_name'        => 'nullable|array',
            'meta_key'                        => 'nullable|array',
            'meta_title'                      => 'nullable|array',
            'meta_description'                => 'nullable|array',
        ]);

        // Handle file uploads

        // Hero Image
        $heroImage = $aboutUs->hero_image;
        if ($request->hasFile('hero_image')) {
            $heroImage = HelperFunc::uploadFile('about_us', $request->file('hero_image'));
        }

        $target_imge = $aboutUs->target_imge;
        if ($request->hasFile('target_imge')) {
            $target_imge = HelperFunc::uploadFile('about_us', $request->file('target_imge'));
        }

        $whyImage  = $aboutUs->why_imge;
        $whyImage2 = $aboutUs->why_imge2;
        $whyImage3 = $aboutUs->why_imge3;

        if ($request->hasFile('why_imge')) {
            $whyImage = HelperFunc::uploadFile('images', $request->file('why_imge'));
        }
        if ($request->hasFile('why_imge2')) {
            $whyImage2 = HelperFunc::uploadFile('images', $request->file('why_imge2'));
        }
        if ($request->hasFile('why_imge3')) {
            $whyImage3 = HelperFunc::uploadFile('images', $request->file('why_imge3'));
        }

        // Update other fields
        $aboutUs->fill($validated);
        $aboutUs->why_imge    = (string) $whyImage;
        $aboutUs->why_imge    = (string) $whyImage;
        $aboutUs->why_imge2   = (string) $whyImage2;
        $aboutUs->why_imge3   = (string) $whyImage3;
        $aboutUs->target_imge = (string) $target_imge;
        $aboutUs->hero_image  = (string) $heroImage;
        $aboutUs->save();

        return response()->json([
            'message' => 'About Us information updated successfully.',
            'data'    => $aboutUs,
        ], 200);
    }

}
