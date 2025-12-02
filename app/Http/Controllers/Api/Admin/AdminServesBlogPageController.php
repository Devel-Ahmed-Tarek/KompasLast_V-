<?php
namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\ServesBlogPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminServesBlogPageController extends Controller
{

    public function index()
    {
        //get data page serves blog
        $data        = ServesBlogPage::first();
        $data->image = asset($data->image);
        return HelperFunc::sendResponse(200, 'done', $data);

    }

    public function update(Request $request)
    {
        // Validate the input data
        $validator = Validator::make($request->all(), [
            'title'       => 'nullable|string',
            'sub_title'   => 'nullable|string',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Validate and store the validated data
        $validated = $validator->validate();

        // Find the serves blog page by ID
        $servesBlogPage = ServesBlogPage::first();

        // If serves blog page not found, return an error response
        if (! $servesBlogPage) {
            return HelperFunc::sendResponse(404, 'Not Found');
        }

        // Update translatable attributes if they exist
        foreach ($servesBlogPage->getTranslatableAttributes() as $field) {
            if (isset($validated[$field]) && is_array($validated[$field])) {
                foreach ($validated[$field] as $locale => $value) {
                    $servesBlogPage->setTranslation($field, $locale, $value);
                }
            }
        }

        // Check if an image is uploaded
        if ($request->hasFile('image')) {

            // Delete the old image if it exists
            if ($servesBlogPage->image) {
                HelperFunc::deleteFile($servesBlogPage->image);
            }

            // Upload the new image
            $path               = HelperFunc::uploadFile('serves_blog_pages', $request->file('image'));
            $validated['image'] = $path;
        }

        // Update the serves blog page data
        $servesBlogPage->update($validated);

        // Return the updated serves blog page data
        return HelperFunc::sendResponse(200, 'done', $servesBlogPage);
    }
}
