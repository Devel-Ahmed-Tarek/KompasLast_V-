<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('offer_reminder.subject') }}</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f5f5f5; padding:20px;">
    <div style="max-width:600px;margin:0 auto;background:#ffffff;border:1px solid #e5e7eb;padding:20px;">
        <h2>{{ __('offer_reminder.title') }}</h2>
        <p>{{ __('offer_reminder.intro') }}</p>

        <p>{{ __('offer_reminder.cta_text') }}</p>

        <a href="https://kompassumzug.ch/en/ratings?offer_id={{ $offer->id }}"
           style="padding: 10px 15px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px;">
            {{ __('offer_reminder.button') }}
        </a>
    </div>
</body>
</html>
