<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message to Company</title>
</head>

<body>
    <p>Dear Company,</p>

    <p>{{ $message_body }}</p>

    <br>
    <p>Best regards,</p>
    <p>{{ $user_name }}</p>
    <p>Email: {{ $user_email }}</p>
</body>

</html>
