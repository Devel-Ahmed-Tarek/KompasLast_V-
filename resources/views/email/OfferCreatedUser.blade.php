<!DOCTYPE html>
<html>

<head>
    <title>{{ __('offer.title') }}</title>
</head>

<body>
    <h1>{{ __('offer.title') }}</h1>
    <p>{{ __('offer.greeting') }}</p>
    <p>{{ __('offer.body') }}</p>

    @if(!empty($confirmUrl))
        <p>
            <a href="{{ $confirmUrl }}" style="
                display:inline-block;
                padding:10px 20px;
                background-color:#2563eb;
                color:#ffffff;
                text-decoration:none;
                border-radius:6px;
            ">
                {{ __('offer.confirm_button') }}
            </a>
        </p>
    @endif

    <p>{{ __('offer.followup') }}</p>
    <p>{{ __('offer.thanks') }}</p>
</body>

</html>
