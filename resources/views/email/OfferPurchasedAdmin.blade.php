<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Neuer Offer-Kauf</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f5f5f5; padding:20px;">
<table width="100%" cellspacing="0" cellpadding="0">
    <tr>
        <td align="center">
            <table width="600" cellspacing="0" cellpadding="20" style="background:#ffffff; border:1px solid #e5e7eb;">
                <tr>
                    <td>
                        <h2 style="margin-top:0;">🧾 Neuer Offer-Kauf</h2>
                        <p>Hallo AuftragKompass-Team,</p>
                        <p>
                            Die Firma <strong>{{ $company->name ?? '' }}</strong> hat soeben einen neuen Offer gekauft.
                        </p>
                        <p>
                            <strong>Offer-ID:</strong> {{ $offer->id }}<br>
                            <strong>Offer-Name:</strong> {{ $offer->name }}<br>
                            <strong>Firma:</strong> {{ $company->name }} (ID: {{ $company->id }})<br>
                            <strong>Preis:</strong> {{ number_format($price, 2, ',', '\'') }} CHF<br>
                            <strong>Kaufdatum:</strong> {{ now()->format('d.m.Y H:i') }}
                        </p>
                        <p>
                            Diese Nachricht wurde automatisch an <strong>info@auftagkompass.de</strong> gesendet.
                        </p>
                        <p style="margin-top:30px;">
                            Viele Grüsse<br>
                            Ihr SystemauftragKompass
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>

