<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <title>Reinigung Offertenanfrage</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fa;
            padding: 20px;
            margin: 0;
        }

        .container {
            max-width: 600px;
            background: #ffffff;
            border: 1px solid #ddd;
            margin: auto;
            padding: 20px;
        }

        .header {
            font-size: 14px;
            color: #333;
            margin-bottom: 20px;
        }

        .title {
            font-size: 20px;
            color: #004a99;
            margin-bottom: 15px;
        }

        .info {
            font-size: 14px;
            line-height: 1.6;
        }

        .info b {
            color: #000;
        }

        .button {
            margin-top: 20px;
            text-align: center;
        }

        .btn {
            background-color: #59c14f;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            font-weight: bold;
            border-radius: 5px;
        }

        .footer {
            margin-top: 20px;
            font-size: 12px;
            text-align: center;
            color: #666;
        }

        .footer a {
            color: #004a99;
            text-decoration: none;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="header">Eine neue Reinigung Offertenanfrage ist soeben eingetroffen.</div>

        <div class="title">🧹 Reinigung Offertenanfrage {{ $offer['id'] ?? 'XXXXXX' }}:</div>

        <div class="info">
            <p><b>Kategorie:</b> {{ $offer['category'] ?? 'Umzugsreinigung' }}</p>
            <p><b>Kontaktwunsch:</b> {{ $offer['contact_type'] ?? 'Grobofferte / Beratung' }}</p>
            <p><b>Datum:</b> {{ $offer['date'] ?? 'Mittwoch, 02.07.2025' }}</p>
            <p><b>Objekt:</b> {{ $offer['object'] ?? 'Wohnung' }}</p>
            <p><b>Anzahl Zimmer:</b> {{ $offer['rooms'] ?? '3 Zimmer' }}</p>
            <p><b>Abnahmegarantie:</b> {{ $offer['guarantee'] ?? 'Ja' }}</p>
            <p><b>Objekt Fläche:</b> {{ $offer['area'] ?? '75 m2' }}</p>
            <p><b>Verschmutzung:</b> {{ $offer['dirt_level'] ?? 'Mittel schmutzig' }}</p>
            <p><b>Bereiche:</b> {{ $offer['areas'] ?? 'Laminat, Teppich, Fensterscheiben, Storen/Rolladen' }}</p>
            <p><b>Standort:</b> {{ $offer['location'] ?? '3098 Köniz, CH' }}</p>
            <p><b>Entfernung:</b> {{ $offer['distance'] ?? '7 KM (Luftlinien Distanz)' }}</p>
            <p><b>Fokus:</b> {{ $offer['focus'] ?? 'Qualität' }}</p>
            <p><b>Rabatt:</b> {{ $offer['discount'] ?? '30% Rabatt, weil Person nur Email angegeben hat' }}</p>
        </div>

        <div class="button">
            <a href="#" class="btn">Auftraggeber kontaktieren</a>
        </div>

        <div class="footer">
            <p><a href="#">Anfrage nicht relevant? Filter einstellen</a></p>
            <p><a href="#">Region nicht relevant? Regionen einstellen</a></p>
            <p><a href="#">Weitere Fragen? Zum Supportbereich mit Hilfe Videos</a></p>
        </div>
    </div>

</body>

</html>
