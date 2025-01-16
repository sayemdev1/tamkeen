<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Referral Invitation</title>
</head>
<body>
    <h1>You've been referred by {{ $user_name }}!</h1>
   <h2>Package: {{ $package_name }}</h2> 
    <p>Click the link below to sign up:</p>
    <a href="{{ $referralLink }}">{{ $referralLink }}</a>
</body>
</html>
