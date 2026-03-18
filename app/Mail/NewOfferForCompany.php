<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewOfferForCompany extends Mailable
{
    use Queueable, SerializesModels;

    public $offer;
    public $company;
    public $price;
    public $locale;

    /**
     * @param  mixed      $offer
     * @param  mixed      $company
     * @param  float|null $price
     * @param  string|null $locale
     */
    public function __construct($offer, $company, ?float $price = null, ?string $locale = null)
    {
        $this->offer   = $offer;
        $this->company = $company;
        $this->price   = $price;

        // حاول نستخدم لغة الشركة لو موجودة، وإلا خُد اللي جاي من البراميتر أو default=en
        $this->locale = $locale
            ?? ($company->lang ?? $company->language ?? null)
            ?? 'de';
    }

    public function build()
    {
        app()->setLocale($this->locale);

        return $this->view('email.NewOfferForCompany')
            ->subject(__('new_offer_company.subject'));
    }
}

