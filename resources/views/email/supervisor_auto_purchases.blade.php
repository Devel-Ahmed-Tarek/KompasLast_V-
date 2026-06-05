<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('supervisor_auto_purchases.subject', ['id' => $offer->id]) }}</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f5f5f5; padding:20px;">
<table width="100%" cellspacing="0" cellpadding="0">
    <tr>
        <td align="center">
            <table width="600" cellspacing="0" cellpadding="20" style="background:#ffffff; border:1px solid #e5e7eb;">
                <tr>
                    <td>
                        <h2 style="margin-top:0;">{{ __('supervisor_auto_purchases.title') }}</h2>
                        <p>{{ __('supervisor_auto_purchases.intro') }}</p>
                        <p>
                            <strong>{{ __('supervisor_auto_purchases.offer_id') }}:</strong> {{ $offer->id }}<br>
                            <strong>{{ __('supervisor_auto_purchases.offer_name') }}:</strong> {{ $offer->name }}
                        </p>
                        <ul style="margin:16px 0; padding-left:20px;">
                            @foreach($purchases as $purchase)
                                <li style="margin-bottom:8px;">
                                    <strong>{{ $purchase['company']->name }}</strong>
                                    (ID: {{ $purchase['company']->id }}) —
                                    {{ number_format($purchase['price'], 2, ',', '\'') }} CHF
                                </li>
                            @endforeach
                        </ul>
                        <p style="margin-top:30px;">
                            {{ __('supervisor_auto_purchases.regards') }}<br>
                            {{ __('supervisor_auto_purchases.system') }}
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
