<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('offer_purchased_admin.subject') }}</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f5f5f5; padding:20px;">
<table width="100%" cellspacing="0" cellpadding="0">
    <tr>
        <td align="center">
            <table width="600" cellspacing="0" cellpadding="20" style="background:#ffffff; border:1px solid #e5e7eb;">
                <tr>
                    <td>
                        <h2 style="margin-top:0;">{{ __('offer_purchased_admin.title') }}</h2>
                        <p>{{ __('offer_purchased_admin.hello') }}</p>
                        <p>
                            {{ __('offer_purchased_admin.intro', ['company_name' => $company->name ?? '' ]) }}
                        </p>
                        <p>
                            <strong>{{ __('offer_purchased_admin.offer_id') }}:</strong> {{ $offer->id }}<br>
                            <strong>{{ __('offer_purchased_admin.offer_name') }}:</strong> {{ $offer->name }}<br>
                            <strong>{{ __('offer_purchased_admin.company') }}:</strong> {{ $company->name }} (ID: {{ $company->id }})<br>
                            <strong>{{ __('offer_purchased_admin.price') }}:</strong>
                            {{ number_format($price, 2, app()->getLocale() === 'de' ? ',' : '.', '\'') }} CHF<br>
                            <strong>{{ __('offer_purchased_admin.date') }}:</strong> {{ now()->format('d.m.Y H:i') }}
                        </p>
                        <p>
                            {{ __('offer_purchased_admin.info_note', ['email' => 'info@auftagkompass.de']) }}
                        </p>
                        <p style="margin-top:30px;">
                            {{ __('offer_purchased_admin.regards') }}<br>
                            {{ __('offer_purchased_admin.system') }}
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>

