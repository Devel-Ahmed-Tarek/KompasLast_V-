<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\TypeTips;
use Illuminate\Http\Request;

class AdminTypeTipsController extends Controller
{
    public function index(Request $request)
    {
        $data = TypeTips::where('type_id', $request->type_id)->get();
        return HelperFunc::sendResponse(200, 'done', $data);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type_id' => 'required|exists:types,id',
            'name' => 'required|array', // التأكد من أن الاسم متعدد اللغات
            'name.en' => 'required|string',
            'name.de' => 'nullable|string',
            'name.fr' => 'nullable|string',
            'name.it' => 'nullable|string',
        ]);

        $typeTip = TypeTips::create([
            'type_id' => $data['type_id'],
            'name' => $data['name'],
        ]);

        return response()->json([
            'message' => 'Type Tip created successfully',
            'data' => $typeTip,
        ], 201);
    }
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'sometimes|array', // قبول البيانات إذا أُرسلت كـ JSON
            'name.en' => 'sometimes|string', // تحديث الإنجليزية إذا تم إرسالها
            'name.de' => 'sometimes|string', // تحديث الألمانية إذا تم إرسالها
            'name.fr' => 'sometimes|string', // تحديث الفرنسية إذا تم إرسالها
            'name.it' => 'sometimes|string', // تحديث الإيطالية إذا تم إرسالها
        ]);
        $typeTip = TypeTips::findOrFail($id);
        // التحقق من وجود كل لغة وتحديثها إذا أُرسلت
        if (isset($data['name']['en'])) {
            $typeTip->setTranslation('name', 'en', $data['name']['en']);
        }
        if (isset($data['name']['de'])) {
            $typeTip->setTranslation('name', 'de', $data['name']['de']);
        }
        if (isset($data['name']['fr'])) {
            $typeTip->setTranslation('name', 'fr', $data['name']['fr']);
        }
        if (isset($data['name']['it'])) {
            $typeTip->setTranslation('name', 'it', $data['name']['it']);
        }

        // حفظ التحديثات
        $typeTip->save();

        return response()->json([
            'message' => 'Type Tip updated successfully',
            'data' => $typeTip,
        ], 200);
    }

    public function destroy($id)
    {
        $typeTip = TypeTips::findOrFail($id);

        $typeTip->delete();

        return response()->json([
            'message' => 'Type Tip deleted successfully',
        ], 200);
    }

}
