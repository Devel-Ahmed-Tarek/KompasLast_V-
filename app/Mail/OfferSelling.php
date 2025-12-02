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

    /**
     * Create a new message instance.
     *
     * @param array $offer
     */
    public function __construct($offer)
    {
        $this->offer = $offer;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->view('email.OfferSelling')
            ->subject('New Offer Created')->with([
            'offer' => $this->offer,
        ]);
    }

}
