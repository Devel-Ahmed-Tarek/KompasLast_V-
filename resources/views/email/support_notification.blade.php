<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>
    <p>Dear Support,</p>
    <p>The complaint with the following details has been {{ $action }}:</p>
    <ul>
        <li><strong>User Name:</strong> {{ $complaint->user_name }}</li>
        <li><strong>Email:</strong> {{ $complaint->email }}</li>
        <li><strong>Phone:</strong> {{ $complaint->phone }}</li>
        <li><strong>Complaint:</strong> {{ $complaint->complain }}</li>
    </ul>
    <p>Best regards,<br>Compass Team</p>

</body>

</html>
