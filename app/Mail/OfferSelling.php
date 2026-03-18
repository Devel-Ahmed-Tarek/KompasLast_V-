<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class OfferSelling extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The offer data.
     *
     */
    public $offer;
    public $locale;

    /**
     * Create a new message instance.
     *
     * @param array       $offer
     * @param string|null $locale
     */
    public function __construct($offer, ?string $locale = null)
    {
        $this->offer  = $offer;
        // لو الفورم فيها لغة نبعتهالك هنا، وإلا خليه ألماني ديفولت
        $this->locale = $locale ?? 'de';
    }

    /**
     * Build the message.
     */
    public function build()
    {
        app()->setLocale($this->locale);

        return $this->view('email.OfferSelling')
            ->subject(__('offer_selling.subject'))
            ->with([
                'offer' => $this->offer,
            ]);
    }

}
