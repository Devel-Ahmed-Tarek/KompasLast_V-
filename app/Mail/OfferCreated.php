<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OfferCreated extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The offer data.
     *
     * @var array
     */
    public $locale;

    /**
     * Create a new message instance.
     *
     * @param string $locale
     */
    public function __construct(string $locale)
    {
        $this->locale = $locale;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->view('email.OfferCreatedUser')
            ->subject('New Offer Created');
    }
}
