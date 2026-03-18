<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('support_question.title', ['name' => $emailData['name']]) }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
        }

        h2 {
            color: #007BFF;
        }

        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #888;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>{{ __('support_question.title', ['name' => $emailData['name']]) }}</h2>
        <p><strong>{{ __('support_question.email') }}:</strong> {{ $emailData['email'] }}</p>
        <p><strong>{{ __('support_question.question_label') }}:</strong></p>
        <p>{{ $emailData['question'] }}</p>
        <p>{{ __('support_question.thanks') }}</p>
    </div>
    <div class="footer">
        {{ __('support_question.footer') }}
    </div>
</body>

</html>
