<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('supervisor_offer_confirmed.subject', ['id' => $offer->id]) }}</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f5f5f5; padding:20px;">
<table width="100%" cellspacing="0" cellpadding="0">
    <tr>
        <td align="center">
            <table width="600" cellspacing="0" cellpadding="20" style="background:#ffffff; border:1px solid #e5e7eb;">
                <tr>
                    <td>
                        <h2 style="margin-top:0;">{{ __('supervisor_offer_confirmed.title') }}</h2>
                        <p>{{ __('supervisor_offer_confirmed.intro') }}</p>
                        <p>
                            <strong>{{ __('supervisor_offer_confirmed.offer_id') }}:</strong> {{ $offer->id }}<br>
                            <strong>{{ __('supervisor_offer_confirmed.offer_name') }}:</strong> {{ $offer->name }}<br>
                            <strong>{{ __('supervisor_offer_confirmed.service_type') }}:</strong> {{ optional($offer->type)->name }}<br>
                            <strong>{{ __('supervisor_offer_confirmed.email') }}:</strong> {{ $offer->email }}<br>
                            <strong>{{ __('supervisor_offer_confirmed.phone') }}:</strong> {{ $offer->phone }}<br>
                            <strong>{{ __('supervisor_offer_confirmed.country') }}:</strong> {{ $offer->country }}<br>
                            <strong>{{ __('supervisor_offer_confirmed.city') }}:</strong> {{ $offer->city }}<br>
                            <strong>{{ __('supervisor_offer_confirmed.execution_date') }}:</strong> {{ $offer->execution_date ?? $offer->date }}
                        </p>
                        <p style="margin-top:16px; padding:12px; background:#f0f9ff; border-left:4px solid #2563eb;">
                            @if($distributionRan)
                                {{ __('supervisor_offer_confirmed.distribution_ran') }}
                            @else
                                {{ __('supervisor_offer_confirmed.distribution_blocked') }}
                            @endif
                        </p>
                        <p style="margin-top:30px;">
                            {{ __('supervisor_offer_confirmed.regards') }}<br>
                            {{ __('supervisor_offer_confirmed.system') }}
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
