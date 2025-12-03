<?php
namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\ContactPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminContactPageController extends Controller
{
    // Display the first contact page
    public function index()
    {
        $contactPage = ContactPage::first();

        if (! $contactPage) {
            return response()->json([
                'status'  => 404,
                'message' => 'No Contact Page Found',
            ]);
        }

        // Convert image path to full URL
        if ($contactPage->image) {
            $contactPage->image = asset($contactPage->image);
        }

        return response()->json([
            'status'  => 200,
            'message' => 'Contact Page Retrieved',
            'data'    => $contactPage,
        ]);
    }

    // Update the contact page
    public function update(Request $request, $id)
    {
        $contactPage = ContactPage::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title'            => 'sometimes|array',
            'sub_title'        => 'sometimes|array',
            'description'      => 'sometimes|array',
            'form_title'       => 'sometimes|array',
            'form_sub_title'   => 'sometimes|array',
            'information'      => 'sometimes|array',
            'meta_key'         => 'sometimes|array',
            'meta_title'       => 'sometimes|array',
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
            $contactPage->image = HelperFunc::uploadFile('contact_pages', $request->file('image'));
        }

        $contactPage->save();

        return response()->json([
            'status'  => 200,
            'message' => 'Contact Page Updated Successfully',
            'data'    => $contactPage,
        ]);
    }
}
