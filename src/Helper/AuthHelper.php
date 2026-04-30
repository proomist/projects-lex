<?php

declare(strict_types=1);

namespace App\Helper;

use Firebase\JWT\JWT;
use Exception;

class AuthHelper
{
    /**
     * Argon2id ile parola hashi üretir.
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID);
    }

    /**
     * Parolanın doğruluğunu kontrol eder.
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Kullanıcı ID ve Rolü içeren JWT oluşturur.
     * jti (JWT ID) claim'i ile token blacklist desteği sağlar.
     * iss/aud claim'leri ile audience binding sağlar.
     */
    public static function createJwt(int $userId, string $role): string
    {
        $secretKey = $_ENV['JWT_SECRET_KEY'];
        $issuedAt = time();
        $expirationTime = $issuedAt + (8 * 3600); // 8 Saat geçerlilik süresi

        $payload = [
            'iss' => $_ENV['APP_DOMAIN'] ?? 'proomist-lex',
            'aud' => $_ENV['APP_DOMAIN'] ?? 'proomist-lex',
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'jti' => bin2hex(random_bytes(16)),
            'data' => [
                'user_id' => $userId,
                'role' => $role
            ]
        ];

        return JWT::encode($payload, $secretKey, 'HS256');
    }
}
