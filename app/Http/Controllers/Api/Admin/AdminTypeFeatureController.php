<?php
namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\TypeFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminTypeFeatureController extends Controller
{
    public function index(Request $request)
    {
        $data = TypeFeature::where('type_id', $request->type_id)->get();

        // Map through the collection and add the full URL for the image
        $data = $data->map(function ($item) {
            $item->image = asset($item->image);
            return $item;
        });

        return HelperFunc::sendResponse(200, 'done', $data);
    }

    public function store(Request $request)
    {
        // التحقق من المدخلات
        $data = $request->validate([
            'type_id'       => 'required|exists:types,id',
            'image'         => 'file|image|mimes:jpeg,png,jpg,gif|max:2048',
            'title'         => 'nullable|array',
            'title.*'       => 'string',
            'description'   => 'nullable|array',
            'description.*' => 'string',
        ]);

        $image = HelperFunc::uploadFile('/images', $request->file("image"));

        // إنشاء أو تحديث الميزة
        $feature = TypeFeature::create([
            'type_id'     => $data['type_id'],
            'image'       => $image,
            'title'       => $data['title'],
            'description' => $data['description'],
        ]);

        // استجابة JSON
        return response()->json([
            'message' => 'Feature created successfully',
            'data'    => $feature,
        ], 201);
    }

    /**
     * تحديث نوع الميزة
     */
    public function update(Request $request, $id)
    {
        // Retrieve the feature or fail
        $feature = TypeFeature::findOrFail($id);

        // Validate inputs
        $Validator = Validator::make($request->all(), [
            'image'         => 'nullable|file|image|mimes:jpeg,png,jpg,gif|max:2048',
            'title'         => 'nullable|array',
            'title.*'       => 'string',
            'description'   => 'nullable|array',
            'description.*' => 'string',
        ]);

        if ($Validator->fails()) {
            return HelperFunc::sendResponse(422, 'هناك رسائل تحقق', $Validator->messages()->all());
        }

        $validated = $Validator->validate();
        foreach ($feature->getTranslatableAttributes() as $field) {
            if (isset($validated[$field])) {
                foreach ($validated[$field] as $locale => $value) {
                    $feature->setTranslation($field, $locale, $value);
                }
            }
        }
        // Handle the image upload if a new image is provided
        if ($request->hasFile('image')) {
            // Delete the old image
            // HelperFunc::deleteFile($feature->image);

            // Upload the new image
            $feature->image = HelperFunc::uploadFile('/images', $request->file('image'));
        }
        // Save the updated feature
        $feature->save();

        // Return JSON response
        return response()->json([
            'message' => 'Feature updated successfully',
            'data'    => $feature,
        ], 200);
    }

    public function destroy($id)
    {
        $typeTip = TypeFeature::findOrFail($id);

        $typeTip->delete();

        return response()->json([
            'message' => 'Type Tip deleted successfully',
        ], 200);
    }

}
