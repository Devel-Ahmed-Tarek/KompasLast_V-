<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Neues Angebot verfügbar</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f5f5f5; padding:20px;">
<table width="100%" cellspacing="0" cellpadding="0">
    <tr>
        <td align="center">
            <table width="600" cellspacing="0" cellpadding="20" style="background:#ffffff; border:1px solid #e5e7eb;">
                <tr>
                    <td>
                        <h2 style="margin-top:0;">📢 Neues passendes Angebot für Sie</h2>
                        <p>Guten Tag {{ $company->name ?? '' }},</p>
                        <p>
                            es wurde soeben ein neues Angebot erstellt, das zu Ihren Einstellungen passt
                            (Kategorie und Region).
                        </p>
                        <p>
                            <strong>Offer-ID:</strong> {{ $offer->id }}<br>
                            <strong>Offer-Name:</strong> {{ $offer->name }}<br>
                            @if($price !== null)
                                <strong>Preis pro Offer:</strong> {{ number_format($price, 2, ',', '\'') }} CHF<br>
                            @endif
                            <strong>PLZ / Ort:</strong> {{ $offer->zipcode }} {{ $offer->city }}<br>
                            <strong>Datum:</strong> {{ optional($offer->date)->format('d.m.Y') }}
                        </p>
                        <p>
                            Bitte loggen Sie sich in Ihr AuftragKompass-Firmenkonto ein und prüfen Sie das Angebot im Shop.
                        </p>
                        <p style="margin-top:30px;">
                            Freundliche Grüsse<br>
                            Ihr AuftragKompass-Team
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>

