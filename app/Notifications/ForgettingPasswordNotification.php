<?php
namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ForgettingPasswordNotification extends Notification
{
    protected $otp;

    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(__('auth.otp_subject'))                            // موضوع الرسالة
            ->greeting(__('auth.hello') . ' ' . $notifiable->name . ',') // ترحيب بالمستخدم
            ->line(__('auth.otp_message'))                               // نص الرسالة
            ->line(__('auth.your_otp') . ' ' . $this->otp)               // عرض الـ OTP
            ->line(__('auth.otp_expiry'))                                // تنبيه بانتهاء الصلاحية
            ->line(__('auth.otp_ignore'))                                // تنبيه بتجاهل الرسالة إذا لم يطلبها المستخدم
            ->salutation(__('auth.thank_you'));                          // التوقيع
    }
}
