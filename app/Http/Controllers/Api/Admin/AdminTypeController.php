<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\Type;
use Illuminate\Http\Request;

// use Illuminate\Support\Facades\Request;

class AdminTypeController extends Controller
{
    // عرض جميع الأنواع
    public function index()
    {
        $types = Type::all();
        return response()->json($types, 200);
    }

    // عرض نوع محدد
    public function show($id)
    {
        $type = Type::find($id);
        if (!$type) {
            return response()->json(['message' => 'Type not found'], 404);
        }

        return response()->json($type, 200);
    }

    // تحديث نوع موجود
    public function update(Request $request, $id)
    {
        $type = Type::find($id);
        if (!$type) {
            return response()->json(['message' => 'Type not found'], 404);
        }

        $type->price = $request->price;
        $type->save();

        $type->update($request->all());
        return response()->json($type, 200);
    }

    public function store(Request $request)
    {
        // Validation
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|array',
            'name.en' => 'required|string',
            'name.de' => 'nullable|string',
            'name.fr' => 'nullable|string',
            'name.it' => 'nullable|string',
            'name.ar' => 'nullable|string',
            'price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->errors());
        }

        try {
        // Save the `type` model
        $type = new Type();
        $type->name = $request->name; // Save multilingual field as JSON
            $type->price = $request->price;
        $type->save();

            return HelperFunc::sendResponse(201, 'Type created successfully', $type);
        } catch (\Exception $e) {
            return HelperFunc::sendResponse(500, 'An error occurred: ' . $e->getMessage(), []);
        }
    }

    // حذف نوع
    public function destroy($id)
    {
        $type = Type::find($id);
        if (!$type) {
            return response()->json(['message' => 'Type not found'], 404);
        }

        $type->delete();
        return response()->json(['message' => 'Type deleted'], 200);
    }
}
