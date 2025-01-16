<?php

// app/Services/JwtService.php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

class JwtService
{
    private $secretKey;

    public function __construct()
    {
        $this->secretKey = config('app.jwt_secret');// Define this in your .env file
    }




    public function generateToken($referredById, $packageId,$level)
    {
        $payload = [
            'iss' => 'tamkeen.center', // Issuer
            'referred_by_id' => $referredById, // ID of the user who referred this user
            'package_id' => $packageId, // ID of the package
            'level' => $level ,
            'iat' => time(), // Issued at
            'exp' => time() + 3600, // Expiration time (1 hour)
        ];

        return JWT::encode($payload, $this->secretKey, 'HS256');
    }
}
