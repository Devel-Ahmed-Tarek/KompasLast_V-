<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Http\Resources\ShoppingListResource;
use App\Models\Shopping_list;
use Illuminate\Http\Request;

class AdminOrderShopController extends Controller
{
    public function index(Request $request)
    {

        $orders = Shopping_list::with('company', 'offer')
            ->when($request->type, function ($q) use ($request) {
                $q->where('type', $request->type);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        // $orders = Shopping_list ::where('type','S') ->with('company', 'offer')->orderBy('created_at', 'desc')->paginate(10);
        // $orders = Shopping_list ::where('type','D') ->with('company', 'offer')->orderBy('created_at', 'desc')->paginate(10);

        // Use a resource to format the data (assuming a ShoppingListResource exists)
        $ordersResource = ShoppingListResource::collection($orders);

        // Use HelperFunc pagination to structure the response
        return HelperFunc::pagination($orders, $ordersResource);
    }

    public function store(Request $request)
    {

        Shopping_list::create([
            'user_id' => $request->user_id,
            'offer_id' => $request->offer_id,
            'type' => $request->type,
        ]);
        return HelperFunc::apiResponse(true, 200, []);
    }

}
