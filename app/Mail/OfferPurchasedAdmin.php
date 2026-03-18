<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OfferPurchasedAdmin extends Mailable
{
    use Queueable, SerializesModels;

    public $offer;
    public $company;
    public $price;
    public $locale;

    /**
     * @param  mixed      $offer
     * @param  mixed      $company
     * @param  float      $price
     * @param  string|null $locale
     */
    public function __construct($offer, $company, $price, ?string $locale = null)
    {
        $this->offer   = $offer;
        $this->company = $company;
        $this->price   = $price;
        // لغة الأدمن، غالباً ألماني، بس نخليها مرنة
        $this->locale  = $locale ?? 'de';
    }

    public function build()
    {
        app()->setLocale($this->locale);

        return $this->view('email.OfferPurchasedAdmin')
            ->subject(__('offer_purchased_admin.subject'));
    }
}

