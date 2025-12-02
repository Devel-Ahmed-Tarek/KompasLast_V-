<?php
namespace App\Http\Controllers;

use App\Helpers\HelperFunc;
use App\Models\BlogsPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BlogsPageController extends Controller
{
    public function index()
    {
        $data = BlogsPage::first();
        if (! $data) {
            return HelperFunc::sendResponse(404, 'Blogs Page not found');
        }
        $data->image = asset($data->image);

        return HelperFunc::sendResponse(200, '', $data);

    }

    public function update(Request $request, $id)
    {
        $contactPage = BlogsPage::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title'            => 'sometimes|array',
            'sub_title'        => 'sometimes|array',
            'description'      => 'sometimes|array',
            'blog_categories'  => 'sometimes|array',
            'meta_title'       => 'sometimes|array',
            'meta_key'         => 'sometimes|array',
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
        foreach ($contactPage->getTranslatableAttributes() as $field) {
            if (isset($validated[$field])) {
                foreach ($validated[$field] as $locale => $value) {
                    $contactPage->setTranslation($field, $locale, $value);
                }
            }
        }

        // Update the image
        if ($request->hasFile('image')) {
            // Delete the old image if it exists
            if ($contactPage->image) {
                HelperFunc::deleteFile($contactPage->image);
            }

            // Upload the new image
            $contactPage->image = HelperFunc::uploadFile('blog_pages', $request->file('image'));
        }

        $contactPage->save();

        return response()->json([
            'status'  => 200,
            'message' => 'Blog Page Updated Successfully',
            'data'    => $contactPage,
        ]);
    }
}
