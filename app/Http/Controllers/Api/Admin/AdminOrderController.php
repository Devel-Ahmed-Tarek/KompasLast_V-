<?php
namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\order;
use App\Models\User;
use App\Notifications\PaymentNotification;

class AdminOrderController extends Controller
{

    public function pending()
    {
        $orders = Order::with('user')->orderBy('id', 'desc')->where('status', 'pending')->paginate(10);
        // تعديل الصورة لكل عنصر في مجموعة البيانات
        $orders->getCollection()->transform(function ($order) {
            if ($order->image) {
                $order->image = asset($order->image);
            }
            return $order;
        });
        return HelperFunc::pagination($orders, $orders->items());
    }

    // جلب الطلبات بالحالة "مؤكدة"
    public function confirmed()
    {
        $orders = Order::with('user')->orderBy('id', 'desc')->where('status', 'confirmed')->paginate(10);
        // تعديل الصورة لكل عنصر في مجموعة البيانات
        $orders->getCollection()->transform(function ($order) {
            if ($order->image) {
                $order->image = asset($order->image);
            }
            return $order;
        });
        return HelperFunc::pagination($orders, $orders->items());

    }

    // جلب الطلبات بالحالة "ملغية"
    public function canceled()
    {
        $orders = Order::with('user')->orderBy('id', 'desc')->where('status', 'canceled')->paginate(10);
        // تعديل الصورة لكل عنصر في مجموعة البيانات
        $orders->getCollection()->transform(function ($order) {
            if ($order->image) {
                $order->image = asset($order->image);
            }
            return $order;
        });
        return HelperFunc::pagination($orders, $orders->items());
    }
    public function all()
    {
        // استرجاع البيانات مع الترتيب والتجزئة
        $orders = Order::with('user')->orderBy('id', 'desc')->paginate(10);

        // تعديل الصورة لكل عنصر في مجموعة البيانات
        $orders->getCollection()->transform(function ($order) {
            if ($order->image) {
                $order->image = asset($order->image);
            }
            return $order;
        });

        // إعادة البيانات بشكل مُجزّأ باستخدام دالة المساعدة
        return HelperFunc::pagination($orders, $orders->items());
    }

    // تغيير حالة الطلب
    public function ChangeStatus(order $order, $status)
    {
        if (! in_array($status, ['confirmed', 'canceled'])) {
            return response()->json(['message' => 'Invalid status provided.'], 400);
        }
        $order->status = $status;
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
        if ($status == 'confirmed') {
            // إضافة إلى المحفظة
            $wallet = User::findOrFail($order->user_id)->wallet;
            $wallet->amount += $order->amount;
            $wallet->save();

            // إرسال إشعار للمستخدم بتأكيد الدفع
            $user->notify(new PaymentNotification($data));

            return response()->json(['message' => 'Order confirmed successfully.', 'order' => $order], 200);
        } else {
            $data['mgs'] = [
                'en' => 'Cancel company payment: ' . $order->amount . ' CHF',
                'de' => 'Unternehmenszahlung stornieren: ' . $order->amount . ' CHF',
                'it' => 'Annulla il pagamento dell\'azienda: ' . $order->amount . ' CHF',
                'fr' => 'Annuler le paiement de l\'entreprise: ' . $order->amount . ' CHF',
            ];
            $data['status'] = 'canceled';
            // إرسال إشعار للمستخدم بإلغاء الدفع
            $user->notify(new PaymentNotification($data));

            return response()->json(['message' => 'Order canceled successfully.', 'order' => $order], 200);
        }
    }
}
