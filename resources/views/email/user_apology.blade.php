<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ __('user_apology.subject') }}</title>
</head>

<body>
    <p>{{ __('user_apology.greeting', ['name' => $complaint->user_name]) }}</p>
    <p>{{ __('user_apology.intro') }}</p>
    <ul>
        <li><strong>{{ __('user_apology.email') }}:</strong> {{ $complaint->email }}</li>
        <li><strong>{{ __('user_apology.complaint') }}:</strong> {{ $complaint->complain }}</li>
    </ul>
    <p>{{ __('user_apology.apology') }}</p>
    <p>{{ __('user_apology.regards') }}<br>{{ __('user_apology.team') }}</p>

</body>

</html>
