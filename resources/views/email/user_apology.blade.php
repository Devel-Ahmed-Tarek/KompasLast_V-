<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>
    <p>Dear {{ $complaint->user_name }},</p>
    <p>We regret to inform you that your complaint has been rejected. Below are the details:</p>
    <ul>
        <li><strong>Email:</strong> {{ $complaint->email }}</li>
        <li><strong>Complaint:</strong> {{ $complaint->complain }}</li>
    </ul>
    <p>We apologize for the inconvenience caused. Please feel free to contact us if you have any further questions.</p>
    <p>Best regards,<br>Compass Team</p>

</body>

</html>
