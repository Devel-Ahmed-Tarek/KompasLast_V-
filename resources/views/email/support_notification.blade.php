<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $action === 'approved' ? __('support_notification.subject_approved') : __('support_notification.subject_rejected') }}</title>
</head>

<body>
    <p>{{ __('support_notification.greeting') }}</p>
    <p>{{ __('support_notification.body', ['action' => $action]) }}</p>
    <ul>
        <li><strong>{{ __('support_notification.user_name') }}:</strong> {{ $complaint->user_name }}</li>
        <li><strong>{{ __('support_notification.email') }}:</strong> {{ $complaint->email }}</li>
        <li><strong>{{ __('support_notification.phone') }}:</strong> {{ $complaint->phone }}</li>
        <li><strong>{{ __('support_notification.complaint') }}:</strong> {{ $complaint->complain }}</li>
    </ul>
    <p>{{ __('support_notification.regards') }}<br>{{ __('support_notification.team') }}</p>

</body>

</html>
