<?php
namespace App\Services;

use Exception;
use Stripe\Charge;
use Stripe\Stripe;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * إنشاء عملية دفع باستخدام Stripe
     */
    public function charge($amount, $currency = 'usd', $source, $description = "Payment")
    {
        try {
            return Charge::create([
                'amount'      => $amount * 100, // تحويل المبلغ إلى السنتات
                'currency'    => $currency,
                'source'      => $source,
                'description' => $description,
            ]);
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
