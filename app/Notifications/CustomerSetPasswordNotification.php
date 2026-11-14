<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomerSetPasswordNotification extends Notification
{
    public function __construct(
        protected string $otp,
        protected string $setPasswordUrl
    ) {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Passwort festlegen — AuftragKompass')
            ->greeting('Hallo ' . ($notifiable->name ?: '') . ',')
            ->line('Ihr Kundenkonto wurde erstellt. Bitte legen Sie jetzt Ihr Passwort fest.')
            ->line('Ihr Bestätigungscode (OTP): **' . $this->otp . '**')
            ->line('Der Code ist 30 Minuten gültig.')
            ->action('Passwort festlegen', $this->setPasswordUrl)
            ->line('Danach können Sie sich auf der Login-Seite anmelden.')
            ->salutation('Ihr AuftragKompass Team');
    }
}
