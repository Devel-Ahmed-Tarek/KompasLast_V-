<?php
namespace App\Http\Controllers\Api\Company;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\ConfigApp;
use App\Models\order;
use App\Models\User;
use App\Notifications\PaymentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Stripe\StripeClient;

class PaymentController extends Controller
{

    public function createPaymentLink(Request $request)
    {
        $Validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:20',
            'lang'   => 'required',
        ]);
        if ($Validator->fails()) {
            return HelperFunc::sendResponse(422, 'هناك رسائل تحقق', $Validator->messages()->all());
        }
        $user = Auth::user();

        try {
            $stripe = new StripeClient(config('services.stripe.secret')); // استخدم config بدل المفتاح الثابت

            $config = ConfigApp::first();
            if ($config->add_finance_order == 1) {
                return HelperFunc::sendResponse(200, __('messages.company_add_money_to_wallet_is_stopping'));
            }

            // إنشاء الطلب داخل النظام
            $order          = new order();
            $order->amount  = $request->amount;
            $order->user_id = $user->id;
            $order->image   = 'null';
            $order->save();

            // إرسال إشعار للأدمن
            $admins = User::where('role', 'admin')->get();
            Notification::send($admins, new PaymentNotification([
                "type"    => "paymentStripe",
                "type_id" => $order->id,
                'mgs'     => [
                    'en' => 'You Have a New Order Payment From : ' . $user->name . "amount :" . $request->amount,
                    'de' => 'Sie haben eine neue Zahlungsbestellung von: ' . $user->name . "amount :" . $request->amount],
            ]));

            // روابط النجاح والفشل
            $frontendSuccessUrl = "http://company.kompassumzug.ch/{$request->lang}/admin/payment-success?order=" . urlencode($order);

            // إنشاء منتج في Stripe
            $product = $stripe->products->create([
                'name' => $user->name,
            ]);

            $price = $stripe->prices->create([
                'unit_amount' => $request->amount * 100,
                'currency'    => 'chf',
                'product'     => $product->id,
            ]);

            $paymentLink = $stripe->paymentLinks->create([
                'line_items'       => [
                    [
                        'price'    => $price->id,
                        'quantity' => 1,
                    ],
                ],
                'metadata'         => [
                    'user_id'  => $user->id,
                    'order_id' => $order->id,
                ],
                'after_completion' => [
                    'type'     => 'redirect',
                    'redirect' => [
                        'url' => $frontendSuccessUrl,
                    ],
                ],
            ]);

            return HelperFunc::sendResponse(200, 'Payment link created successfully.', ['payment_link' => $paymentLink->url]);
        } catch (\Exception $e) {
            return HelperFunc::sendResponse(500, 'Failed to create payment link', []);
        }
    }

    public function hoke(Request $request)
    {

        $Validator = Validator::make($request->all(), [
            'user_id'  => 'required',
            'order_id' => 'required',
        ]);
        if ($Validator->fails()) {
            return HelperFunc::sendResponse(422, 'هناك رسائل تحقق', $Validator->messages()->all());
        }

        $order = order::find($request->order_id);

        if ($order->status = 'confirmed') {
            return response()->json(['message' => 'Order confirmed successfully.', 'order' => $order], 200);

        }

        $order->status = 'confirmed';
        $order->save();

        $user = User::findOrFail($order->user_id);
        $data = [
            'type'   => 'payment',
            'mgs'    => [
                'en' => 'confirm-company-payment: ' . $order->amount . ' CHF',
                'de' => 'Bestätigen Sie die Unternehmenszahlung: ' . $order->amount . ' CHF',
                'it' => 'Conferma il pagamento dell\'azienda: ' . $order->amount . ' CHF',
                'fr' => 'Confirmez le paiement de l\'entreprise: ' . $order->amount . ' CHF',
            ],
            'status' => 'confirmed',
        ];

        //send notification to admin
        $admins = User::query()->where('role', 'admin')->where("available_notification", '1')->get();
        Notification::send($admins, new PaymentNotification([
            "type"    => "order_confirmed_stripe",
            "type_id" => $order->id,
            "mgs"     => [
                "en" => "confirm-company-payment: " . $order->amount . " CHF",
                "de" => "Bestätigen Sie die Unternehmenszahlung: " . $order->amount . " CHF",
            ],
        ]));

        // add to wallet
        $wallet = User::findOrFail($order->user_id)->wallet;
        $wallet->amount += $order->amount;
        $wallet->save();

        //send notification to user
        $user->notify(new PaymentNotification($data));

        return HelperFunc::sendResponse(200, 'Order confirmed successfully.', ['order' => $order]);

    }

}
