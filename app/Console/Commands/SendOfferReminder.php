<?php
namespace App\Console\Commands;

use App\Mail\OfferReminderMail;
use App\Models\Offer;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendOfferReminder extends Command
{
    /**
     * اسم الأمر عند تشغيله من التيرمنال
     */
    protected $signature = 'app:send-offer-reminder';

    /**
     * وصف الأمر
     */
    protected $description = 'Send reminder email to users one day after offer execution date';

    /**
     * تنفيذ الأمر
     */
    public function handle()
    {
        // نجيب تاريخ أمس
        $yesterday = Carbon::yesterday()->toDateString();

        // نجيب العروض اللي تنفيذها كان أمس
        $offers = Offer::whereDate('execution_date', $yesterday)->get();

        foreach ($offers as $offer) {
            if ($offer->email) {
                Mail::to($offer->email)->send(new OfferReminderMail($offer));
            }
        }

        $this->info('Reminder emails sent successfully ✅');
    }
}
