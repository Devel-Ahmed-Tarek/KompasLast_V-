<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New customer offer</title>
</head>
<body style="font-family: Arial, sans-serif; background-color:#f5f5f5; padding:24px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:8px;overflow:hidden;">
        <tr>
            <td style="padding:16px 24px;border-bottom:1px solid #e5e5e5;">
                <strong style="font-size:18px;">New customer offer created</strong>
            </td>
        </tr>
        <tr>
            <td style="padding:16px 24px;">
                <p style="margin:0 0 12px 0;">A new offer has been created on AuftragKompass.</p>

                <h4 style="margin:16px 0 8px 0;">Offer details</h4>
                <ul style="margin:0 0 12px 18px;padding:0;">
                    <li><strong>ID:</strong> {{ $offer->id }}</li>
                    <li><strong>Service type:</strong> {{ optional($offer->type)->name }}</li>
                    <li><strong>Name:</strong> {{ $offer->name }}</li>
                    <li><strong>Email:</strong> {{ $offer->email }}</li>
                    <li><strong>Phone:</strong> {{ $offer->phone }}</li>
                    <li><strong>Country:</strong> {{ $offer->country }}</li>
                    <li><strong>City:</strong> {{ $offer->city }}</li>
                    <li><strong>Zipcode:</strong> {{ $offer->zipcode }}</li>
                    <li><strong>Execution date:</strong> {{ $offer->execution_date ?? $offer->date }}</li>
                </ul>

                @if(!empty($offer->Besonderheiten))
                    <h4 style="margin:16px 0 8px 0;">Customer notes</h4>
                    <p style="margin:0 0 12px 0;white-space:pre-line;">{{ $offer->Besonderheiten }}</p>
                @endif

                <p style="margin:16px 0 0 0;font-size:12px;color:#777;">
                    You can review this offer in the admin panel.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>

