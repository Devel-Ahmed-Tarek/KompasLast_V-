<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OfferPurchasedCompany extends Mailable
{
    use Queueable, SerializesModels;

    public $offer;
    public $company;
    public $price;
    public $locale;

    /**
     * @param  mixed       $offer
     * @param  mixed       $company
     * @param  float       $price
     * @param  string|null $locale
     */
    public function __construct($offer, $company, $price, ?string $locale = null)
    {
        $this->offer   = $offer;
        $this->company = $company;
        $this->price   = $price;
        // لغة الشركة (لو عندها)، أو اللي جاية من البراميتر، أو ديفولت ألماني
        $this->locale  = $locale
            ?? ($company->lang ?? $company->language ?? null)
            ?? 'de';
    }

    public function build()
    {
        app()->setLocale($this->locale);

        return $this->view('email.OfferPurchasedCompany')
            ->subject(__('offer_purchased_company.subject'));
    }
}

