<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <title>{{ __('offer_selling.subject') }}</title>
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
        <div class="header">{{ __('offer_selling.header') }}</div>

        <div class="title">
            {{ __('offer_selling.title', ['id' => $offer['id'] ?? 'XXXXXX']) }}
        </div>

        <div class="info">
            <p><b>{{ __('offer_selling.category') }}:</b> {{ $offer['category'] ?? 'Umzugsreinigung' }}</p>
            <p><b>{{ __('offer_selling.contact_type') }}:</b> {{ $offer['contact_type'] ?? 'Grobofferte / Beratung' }}</p>
            <p><b>{{ __('offer_selling.date') }}:</b> {{ $offer['date'] ?? 'Mittwoch, 02.07.2025' }}</p>
            <p><b>{{ __('offer_selling.object') }}:</b> {{ $offer['object'] ?? 'Wohnung' }}</p>
            <p><b>{{ __('offer_selling.rooms') }}:</b> {{ $offer['rooms'] ?? '3 Zimmer' }}</p>
            <p><b>{{ __('offer_selling.guarantee') }}:</b> {{ $offer['guarantee'] ?? 'Ja' }}</p>
            <p><b>{{ __('offer_selling.area') }}:</b> {{ $offer['area'] ?? '75 m2' }}</p>
            <p><b>{{ __('offer_selling.dirt_level') }}:</b> {{ $offer['dirt_level'] ?? 'Mittel schmutzig' }}</p>
            <p><b>{{ __('offer_selling.areas') }}:</b> {{ $offer['areas'] ?? 'Laminat, Teppich, Fensterscheiben, Storen/Rolladen' }}</p>
            <p><b>{{ __('offer_selling.location') }}:</b> {{ $offer['location'] ?? '3098 Köniz, CH' }}</p>
            <p><b>{{ __('offer_selling.distance') }}:</b> {{ $offer['distance'] ?? '7 KM (Luftlinien Distanz)' }}</p>
            <p><b>{{ __('offer_selling.focus') }}:</b> {{ $offer['focus'] ?? 'Qualität' }}</p>
            <p><b>{{ __('offer_selling.discount') }}:</b> {{ $offer['discount'] ?? '30% Rabatt, weil Person nur Email angegeben hat' }}</p>
        </div>

        <div class="button">
            <a href="#" class="btn">{{ __('offer_selling.btn_contact') }}</a>
        </div>

        <div class="footer">
            <p><a href="#">{{ __('offer_selling.link_filter') }}</a></p>
            <p><a href="#">{{ __('offer_selling.link_regions') }}</a></p>
            <p><a href="#">{{ __('offer_selling.link_support') }}</a></p>
        </div>
    </div>

</body>

</html>
