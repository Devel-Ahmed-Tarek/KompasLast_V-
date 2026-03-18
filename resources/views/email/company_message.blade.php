<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ __('company_message.subject', ['name' => $emailData['user_name']]) }}</title>
</head>

<body>
    <p>{{ __('company_message.greeting', ['name' => $emailData['user_name']]) }}</p>
    <p>{{ __('company_message.intro') }}</p>
    <ul>
        <li><strong>{{ __('company_message.email') }}:</strong> {{ $emailData['user_email'] }}</li>
        <li><strong>{{ __('company_message.body_label') }}:</strong> {!! $emailData['message_body'] !!}</li>
    </ul>
    <p>{{ __('company_message.apology') }}</p>
    <p>{{ __('company_message.regards') }}<br>{{ __('company_message.team') }}</p>
</body>

</html>
