<?php
namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\FaqPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminFaqPageController extends Controller
{
    public function index()
    {
        // Fetch the first FAQ page
        $data = FaqPage::first();

        if (! $data) {
            return HelperFunc::sendResponse(404, 'FAQ Page not found');
        }

        $images = [];

        // Decode hero_image JSON
        // return $data->hero_image;
        $heroImages = json_decode($data->hero_image, true);

        if ($heroImages) {
            foreach ($heroImages as $lang => $imagePath) {
                $images[$lang] = asset($imagePath); // Generate full URL for each image
            }
        }

        $data->hero_image = $images;

        return HelperFunc::sendResponse(200, 'done', $data);
    }

    public function update(Request $request, $id)
    {
        $imageLanguages = ['en', 'de', 'fr', 'it']; // Define supported languages for images

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
            'hero_image'       => 'nullable|array',      // Multilingual hero images
            'hero_image.*'     => 'file|image|max:2048', // Validate hero images
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation Error', [
                'errors' => $validator->errors(),
            ]);
        }

        // Retrieve existing hero image data
        $heroImages = json_decode($post->hero_image, true) ?: [];

        // Process updated hero image uploads
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

        // Manually set the fields to update
        $post->title            = $validated['title'] ?? $post->title;
        $post->sub_title        = $validated['sub_title'] ?? $post->sub_title;
        $post->form_title       = $validated['form_title'] ?? $post->form_title;
        $post->form_sub_title   = $validated['form_sub_title'] ?? $post->form_sub_title;
        $post->meta_key         = $validated['meta_key'] ?? $post->meta_key;
        $post->meta_title       = $validated['meta_title'] ?? $post->meta_title;
        $post->meta_description = $validated['meta_description'] ?? $post->meta_description;
        $post->hero_image       = $heroImages;

        // Save the updated model
        $post->save();

        return HelperFunc::sendResponse(200, 'FAQ Page updated successfully', $post);
    }

}
