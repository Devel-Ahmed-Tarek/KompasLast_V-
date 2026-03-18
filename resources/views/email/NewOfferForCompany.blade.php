<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('new_offer_company.subject') }}</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f5f5f5; padding:20px;">
<table width="100%" cellspacing="0" cellpadding="0">
    <tr>
        <td align="center">
            <table width="600" cellspacing="0" cellpadding="20" style="background:#ffffff; border:1px solid #e5e7eb;">
                <tr>
                    <td>
                        <h2 style="margin-top:0;">📢 {{ __('new_offer_company.title') }}</h2>
                        <p>{{ __('new_offer_company.hello', ['name' => $company->name ?? '' ]) }}</p>
                        <p>
                            {{ __('new_offer_company.intro') }}
                        </p>
                        <p>
                            <strong>{{ __('new_offer_company.offer_id') }}:</strong> {{ $offer->id }}<br>
                            <strong>{{ __('new_offer_company.offer_name') }}:</strong> {{ $offer->name }}<br>
                            @if($price !== null)
                                <strong>{{ __('new_offer_company.price_per_offer') }}:</strong>
                                {{ number_format($price, 2, app()->getLocale() === 'de' ? ',' : '.', app()->getLocale() === 'de' ? '\'' : '\'') }}
                                CHF<br>
                            @endif
                            <strong>{{ __('new_offer_company.zip_city') }}:</strong> {{ $offer->zipcode }} {{ $offer->city }}<br>
                            <strong>{{ __('new_offer_company.date') }}:</strong>
                            {{ optional($offer->date)->format('d.m.Y') }}
                        </p>
                        <p>
                            {{ __('new_offer_company.action_text') }}
                        </p>
                        <p style="margin-top:30px;">
                            {{ __('new_offer_company.regards') }}<br>
                            {{ __('new_offer_company.team') }}
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>

