<?php
namespace App\Http\Controllers\Api\Company;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrederCompanyResource;
use App\Models\ConfigApp;
use App\Models\order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyOrderController extends Controller
{

    public function index()
    {
        if (! auth()->check()) {
            return response()->json(['msg' => 'User not authenticated'], 401);
        }

        $orders = Order::query()
            ->where('user_id', auth()->user()->id)
            ->orderBy('id', 'desc')
            ->paginate(10);
        return response()->json($orders);
    }

    public function store(Request $request)
    {

        $config = ConfigApp::first();
        if ($config->add_finance_order == 1) {
            return HelperFunc::sendResponse(200, __('messages.company_add_money_to_wallet_is_stopping'));
        }
        $user          = auth()->user();
        $validatedData = $request->validate([
            'amount' => 'required',
            'image'  => 'required|image',
        ]);

        $order          = new Order();
        $order->amount  = $validatedData['amount'];
        $order->user_id = auth()->user()->id;
        $order->image   = HelperFunc::uploadFile('uploads/orders', $request->file('image'));
        $order->save();

        // send notification to Admin
        $admins = User::query()->where('role', 'admin')->where("available_notification")->get();
        HelperFunc::sendMultilangNotification($admins, "order", $order->id, [
            'en' => 'You Have a New Order Payment From : ' . $user->name,
            'de' => 'Sie haben eine neue Zahlungsbestellung von: ' . $user->name,
        ]);
        return HelperFunc::sendResponse(201, __('messages.created_successfully'));
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'amount' => 'required',
            'image'  => 'nullable|image',
        ]);

        $order = Order::findOrFail($id);
        if ($order->status == 'pending') {

            if ($order->user_id !== auth()->user()->id) {
                return response()->json(['msg' => 'Unauthorized'], 403); // التحقق من صلاحيات المستخدم
            }

            $order->amount = $validatedData['amount'];

            if ($request->hasFile('image')) {
                HelperFunc::deleteFile($order->image);
                $order->image = HelperFunc::uploadFile('/orders', $request->file('image'));
            }

            $order->save();
            return HelperFunc::sendResponse(200, __('messages.updated_successfully'));

        }
        return HelperFunc::sendResponse(400, __('messages.dont_have_permissions'));
    }

    public function tap(Request $request)
    {
        if ($request->status == 'all') {
            $orders = Order::query()
                ->where('user_id', auth()->user()->id)
                ->orderBy('id', 'desc')
                ->paginate(10);
        } else {
            $orders = Order::query()
                ->where('user_id', auth()->user()->id)
                ->where('status', $request->status)
                ->orderBy('id', 'desc')
                ->paginate(10);
        }

        return HelperFunc::pagination($orders, OrederCompanyResource::collection($orders));
    }

    public function destroy($id)
    {
        $order = order::findOrFail($id);

        if ($order->user_id !== auth()->user()->id) {
            return response()->json(['msg' => 'Unauthorized'], 403);
        }

        if ($order->status === 'pending') {
            HelperFunc::deleteFile($order->image);
            $order->delete();
            return HelperFunc::sendResponse(200, __('messages.deleted_successfully'));
        }
        return HelperFunc::sendResponse(400, __('messages.dont_have_permissions'));
    }
}
