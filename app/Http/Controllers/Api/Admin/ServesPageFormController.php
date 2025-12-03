<?php
namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Models\ServesPageForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServesPageFormController
{
    public function index(Request $request)
    {
        $data = ServesPageForm::where('type_id', $request->type_id)->get();

        // Map through the collection and add the full URL for the image
        $data = $data->map(function ($item) {
            $item->image = asset($item->image);
            return $item;
        });

        return HelperFunc::sendResponse(200, 'done', $data);
    }

    public function show($id)
    {
        // Find the item
        $form = ServesPageForm::where('type_id', $id)->first();

        if (! $form) {
            return HelperFunc::sendResponse(200, 'Item not found');

        }

        $form->image = asset($form->image);
        return HelperFunc::sendResponse(200, 'done', $form);
    }

    public function store(Request $request)
    {
        // التحقق من المدخلات
        $Validator = Validator::make($request->all(), [
            'type_id'            => 'required|exists:types,id',
            'image'              => 'nullable|file|image|mimes:jpeg,png,jpg,gif|max:2048',
            'title'              => 'nullable|array',
            'title.*'            => 'string',
            'body'               => 'nullable|array',
            'body.*'             => 'string',
            'slug'               => 'nullable|array',
            'slug.*'             => 'string',
            'description'        => 'nullable|array',
            'description.*'      => 'string',
            'meta_key'           => 'nullable|array',
            'meta_key.*'         => 'string',
            'meta_title'         => 'nullable|array',
            'meta_title.*'       => 'string',
            'meta_description'   => 'nullable|array',
            'meta_description.*' => 'string',
        ]);

        if ($Validator->fails()) {
            return HelperFunc::sendResponse(422, 'هناك رسائل تحقق', $Validator->messages()->all());
        }

        $validated = $Validator->validated();

        $image = null;
        if ($request->hasFile('image')) {
            $image = HelperFunc::uploadFile('/images', $request->file("image"));
        }

        $form          = new ServesPageForm();
        $form->type_id = $validated['type_id'];
        $form->image   = $image;

        $translatedFields = $form->getTranslatableAttributes();

        foreach ($translatedFields as $field) {
            if (isset($validated[$field])) {
                foreach ($validated[$field] as $locale => $value) {
                    $form->setTranslation($field, $locale, $value);
                }
            }
        }

        $form->save();

        return HelperFunc::sendResponse(200, 'تم الحفظ بنجاح', $form);
    }
    public function update(Request $request, $id)
    {
        // Validate input
        $Validator = Validator::make($request->all(), [
            'type_id'            => 'sometimes|exists:types,id',
            'image'              => 'nullable|file|image|mimes:jpeg,png,jpg,gif|max:2048',
            'title'              => 'nullable|array',
            'title.*'            => 'string',
            'slug'               => 'nullable|array',
            'slug.*'             => 'string',
            'body'               => 'nullable|array',
            'body.*'             => 'string',
            'description'        => 'nullable|array',
            'description.*'      => 'string',
            'meta_key'           => 'nullable|array',
            'meta_key.*'         => 'string',
            'meta_title'         => 'nullable|array',
            'meta_title.*'       => 'string',
            'meta_description'   => 'nullable|array',
            'meta_description.*' => 'string',
        ]);

        if ($Validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $Validator->messages()->all());
        }

        $validated = $Validator->validated();

        // Find the item
        $form = ServesPageForm::find($id);

        if (! $form) {
            return HelperFunc::sendResponse(404, 'Item not found');
        }

        // Upload and replace image if provided
        if ($request->hasFile('image')) {
            // Optionally delete old image
            HelperFunc::deleteFile($form->image);

            $form->image = HelperFunc::uploadFile('/images', $request->file("image"));
        }

        // Update basic (non-translatable) fields
        if (isset($validated['type_id'])) {
            $form->type_id = $validated['type_id'];
        }

        // Update translatable fields
        $translatedFields = $form->getTranslatableAttributes();

        foreach ($translatedFields as $field) {
            if (isset($validated[$field])) {
                foreach ($validated[$field] as $locale => $value) {
                    $form->setTranslation($field, $locale, $value);
                }
            }
        }

        $form->save();

        return HelperFunc::sendResponse(200, 'Updated successfully', $form);
    }

    public function destroy($id)
    {
        $typeTip = ServesPageForm::findOrFail($id);

        $typeTip->delete();

        return response()->json([
            'message' => 'Type Tip deleted successfully',
        ], 200);
    }
}
