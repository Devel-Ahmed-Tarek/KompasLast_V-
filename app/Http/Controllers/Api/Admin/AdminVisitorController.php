<?php
namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AdminVisitorController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:Website About Page Show', ['only' => ['index']]);
    }

    public function index(Request $request)
    {
        // استعلام الزوار مع جلب البيانات المرتبطة
        $query = Visitor::query()->with('links');

        if ($request->has('search')) {
            $searchTerm = $request->get('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('id', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('ip_address', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('browser', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('country', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('city', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('device', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('created_at', 'LIKE', "%{$searchTerm}%");
            });
        }

        // ترتيب وعرض البيانات مع التصفح
        $paginatedVisitors = $query->orderBy('updated_at', 'desc')
            ->paginate($request->get('per_page', 10));

        // تنسيق الاستجابة باستخدام `HelperFunc`
        return HelperFunc::pagination($paginatedVisitors, $paginatedVisitors->items());
    }

}
