<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Neuer Auftrag gekauft</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f5f5f5; padding:20px;">
<table width="100%" cellspacing="0" cellpadding="0">
    <tr>
        <td align="center">
            <table width="600" cellspacing="0" cellpadding="20" style="background:#ffffff; border:1px solid #e5e7eb;">
                <tr>
                    <td>
                        <h2 style="margin-top:0;">🎉 Glückwunsch zu Ihrem neuen Auftrag!</h2>
                        <p>Guten Tag {{ $company->name ?? '' }},</p>
                        <p>
                            <strong>Sie haben soeben einen neuen Offer gekauft.</strong>
                        </p>
                        <p>
                            <strong>Offer-ID:</strong> {{ $offer->id }}<br>
                            <strong>Name:</strong> {{ $offer->name }}<br>
                            <strong>Preis:</strong> {{ number_format($price, 2, ',', '\'') }} CHF<br>
                            <strong>Datum:</strong> {{ optional($offer->created_at)->format('d.m.Y H:i') }}
                        </p>
                        <p>
                            Bitte loggen Sie sich in Ihr Firmenkonto ein, um die vollständigen Auftragsdetails einzusehen und den Kunden zu kontaktieren.
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

