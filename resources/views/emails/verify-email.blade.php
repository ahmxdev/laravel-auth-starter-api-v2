<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    welcome {{ $user->name }} to our website, please click the link below to verify your email address:
    <a href="{{ $url }}">Verify Email</a>
</body>
</html>
