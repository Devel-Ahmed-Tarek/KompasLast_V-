<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OfferCreated extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The locale for the email.
     *
     * @var string
     */
    public $locale;

    /**
     * Optional confirmation URL.
     *
     * @var string|null
     */
    public $confirmUrl;

    /**
     * Create a new message instance.
     *
     * @param string      $locale
     * @param string|null $confirmUrl
     */
    public function __construct(string $locale, ?string $confirmUrl = null)
    {
        $this->locale     = $locale;
        $this->confirmUrl = $confirmUrl;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        // استخدام لغة الإيميل حسب الـ locale القادم من الـ request
        app()->setLocale($this->locale ?: config('app.locale'));

        return $this->view('email.OfferCreatedUser')
            ->subject(__('offer.title'));
    }
}


