<?php
namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Models\PageCompany;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class AdminPageCompanyController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:Website Companies Page Show', ['only' => ['index']]);
        $this->middleware('can:Website Companies Page update', ['only' => ['update']]);

    }
    // Display the first page company
    public function index()
    {
        $pageCompany = PageCompany::first();

        if (! $pageCompany) {
            return response()->json([
                'status'  => 404,
                'message' => 'No Page Company Found',
            ]);
        }

        // Convert image path to full URL
        if ($pageCompany->image) {
            $pageCompany->image = asset($pageCompany->image);
        }

        return response()->json([
            'status'  => 200,
            'message' => 'Page Company Retrieved',
            'data'    => $pageCompany,
        ]);
    }

    // Update the page company
    public function update(Request $request, $id)
    {
        $pageCompany = PageCompany::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title'            => 'sometimes|array',
            'sub_title'        => 'sometimes|array',
            'description'      => 'sometimes|array',
            'form_title'       => 'sometimes|array',
            'image_title'      => 'sometimes|array',
            'information'      => 'sometimes|array',
            'meta_key'         => 'sometimes|array',
            'slug'             => 'sometimes|array',
            'meta_description' => 'sometimes|array',
            'image'            => 'nullable|file|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 422,
                'message' => 'Validation Error',
                'errors'  => $validator->errors(),
            ]);
        }

        $validated = $validator->validated();

        // Update translatable fields
        foreach ($pageCompany->getTranslatableAttributes() as $field) {
            if (isset($validated[$field])) {
                foreach ($validated[$field] as $locale => $value) {
                    $pageCompany->setTranslation($field, $locale, $value);
                }
            }
        }

        // Update the image
        if ($request->hasFile('image')) {
            // Delete the old image if it exists
            if ($pageCompany->image) {
                HelperFunc::deleteFile($pageCompany->image);
            }

            // Upload the new image
            $pageCompany->image = HelperFunc::uploadFile('/page_companies', $request->file('image'));
        }

        $pageCompany->save();

        return response()->json([
            'status'  => 200,
            'message' => 'Page Company Updated Successfully',
            'data'    => $pageCompany,
        ]);
    }
}
