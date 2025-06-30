<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;

class AnonymousIdService
{
    protected const SECRET_KEY = 'anonymous_secret_key_123';
    public static function encryptUserId($userId): string
    {
        return hash_hmac('sha256', $userId, self::SECRET_KEY);
    }
    public static function decryptUserId(string $encryptedId): int
    {
        throw new \Exception("Decrypt not supported for HMAC.");
    }
}
