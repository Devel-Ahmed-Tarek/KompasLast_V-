<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #182844; }
        h1 { font-size: 20px; margin: 0 0 8px; }
        .muted { color: #667085; }
        .box { border: 1px solid #E4E7EC; padding: 12px; margin-top: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #E4E7EC; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h1>Rechnung {{ $invoice->number }}</h1>
    <p class="muted">{{ $config->name ?? 'AuftragKompass' }}</p>

    <div class="box">
        <strong>Kunde / Unternehmen</strong><br>
        {{ $company->name }}<br>
        {{ $company->email }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Beschreibung</th>
                <th class="right">Betrag</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $invoice->title }}</td>
                <td class="right">{{ number_format($invoice->amount, 2, ',', '.') }} {{ $invoice->currency }}</td>
            </tr>
        </tbody>
    </table>

    <p style="margin-top:24px">
        Status: <strong>{{ strtoupper($invoice->status) }}</strong><br>
        Datum: {{ optional($invoice->created_at)->format('d.m.Y H:i') }}
    </p>
</body>
</html>
