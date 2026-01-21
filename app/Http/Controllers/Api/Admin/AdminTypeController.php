<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminTypeController extends Controller
{
    /**
     * عرض جميع الأنواع الرئيسية مع الفرعية
     */
    public function index(Request $request)
    {
        // Check if we want flat list or hierarchical
        $flat = $request->query('flat', false);
        
        if ($flat) {
            // Return flat list of all types
            $types = Type::with('parent')->orderBy('parent_id')->orderBy('order')->get();
            return response()->json($types, 200);
        }

        // Return hierarchical structure (parents with children)
        $types = Type::whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query->orderBy('order');
            }])
            ->orderBy('order')
            ->get();

        return response()->json($types, 200);
    }

    /**
     * عرض الأنواع الرئيسية فقط (للـ dropdown)
     */
    public function getParentTypes()
    {
        $types = Type::whereNull('parent_id')
            ->orderBy('order')
            ->get(['id', 'name', 'price']);

        return response()->json($types, 200);
    }

    /**
     * عرض الأنواع الفرعية لنوع معين
     */
    public function getChildren($parentId)
    {
        $parent = Type::find($parentId);
        if (!$parent) {
            return response()->json(['message' => 'Parent type not found'], 404);
        }

        $children = $parent->children()->orderBy('order')->get();
        return response()->json($children, 200);
    }

    /**
     * عرض نوع محدد مع الفرعية
     */
    public function show($id)
    {
        $type = Type::with(['parent', 'children'])->find($id);
        if (!$type) {
            return response()->json(['message' => 'Type not found'], 404);
        }

        return response()->json($type, 200);
    }

    /**
     * تحديث نوع موجود
     */
    public function update(Request $request, $id)
    {
        $type = Type::find($id);
        if (!$type) {
            return response()->json(['message' => 'Type not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|array',
            'name.en' => 'sometimes|string',
            'name.de' => 'nullable|string',
            'name.fr' => 'nullable|string',
            'name.it' => 'nullable|string',
            'name.ar' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'parent_id' => 'nullable|exists:types,id',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->errors());
        }

        // Prevent circular reference (type can't be its own parent)
        if ($request->has('parent_id') && $request->parent_id == $id) {
            return HelperFunc::sendResponse(422, 'Type cannot be its own parent', []);
        }

        // Prevent setting parent to one of its children
        if ($request->has('parent_id') && $request->parent_id) {
            $childIds = $type->children()->pluck('id')->toArray();
            if (in_array($request->parent_id, $childIds)) {
                return HelperFunc::sendResponse(422, 'Cannot set a child as parent', []);
            }
        }

        $type->update($request->only(['name', 'price', 'parent_id', 'order', 'is_active']));
        $type->load(['parent', 'children']);

        return response()->json($type, 200);
    }

    /**
     * إنشاء نوع جديد (رئيسي أو فرعي)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|array',
            'name.en' => 'required|string',
            'name.de' => 'nullable|string',
            'name.fr' => 'nullable|string',
            'name.it' => 'nullable|string',
            'name.ar' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'parent_id' => 'nullable|exists:types,id',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->errors());
        }

        try {
            $type = new Type();
            $type->name = $request->name;
            $type->price = $request->price;
            $type->parent_id = $request->parent_id;
            $type->order = $request->order ?? 0;
            $type->is_active = $request->is_active ?? true;
            $type->save();

            $type->load(['parent', 'children']);

            $message = $type->parent_id ? 'Subtype created successfully' : 'Type created successfully';
            return HelperFunc::sendResponse(201, $message, $type);
        } catch (\Exception $e) {
            return HelperFunc::sendResponse(500, 'An error occurred: ' . $e->getMessage(), []);
        }
    }

    /**
     * حذف نوع (يحذف الفرعية معاه)
     */
    public function destroy($id)
    {
        $type = Type::find($id);
        if (!$type) {
            return response()->json(['message' => 'Type not found'], 404);
        }

        // Count children that will be deleted
        $childrenCount = $type->children()->count();
        
        $type->delete(); // Will cascade delete children due to foreign key

        $message = $childrenCount > 0 
            ? "Type and {$childrenCount} subtypes deleted" 
            : 'Type deleted';

        return response()->json(['message' => $message], 200);
    }

    /**
     * إعادة ترتيب الأنواع
     */
    public function reorder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'types' => 'required|array',
            'types.*.id' => 'required|exists:types,id',
            'types.*.order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->errors());
        }

        foreach ($request->types as $typeData) {
            Type::where('id', $typeData['id'])->update(['order' => $typeData['order']]);
        }

        return HelperFunc::sendResponse(200, 'Types reordered successfully', []);
    }

    /**
     * تفعيل/تعطيل نوع
     */
    public function toggleActive($id)
    {
        $type = Type::find($id);
        if (!$type) {
            return response()->json(['message' => 'Type not found'], 404);
        }

        $type->is_active = !$type->is_active;
        $type->save();

        $status = $type->is_active ? 'activated' : 'deactivated';
        return response()->json([
            'message' => "Type {$status}",
            'is_active' => $type->is_active
        ], 200);
    }
}
