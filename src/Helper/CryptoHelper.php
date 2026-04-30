<?php

declare(strict_types=1);

namespace App\Helper;

use Exception;

class CryptoHelper
{
    private const CIPHER = 'aes-256-gcm';

    /**
     * Verilen metni AES-256-GCM ile şifreler.
     * Sonuç olarak IV, Ciphertext ve Tag'i birleştirip base64 formatında döner.
     */
    public static function encrypt(mixed $plaintext): string
    {
        $plaintext = (string)$plaintext;
        $key = $_ENV['AES_ENCRYPTION_KEY'] ?? '';

        if (strlen($key) !== 32) {
            throw new Exception('AES_ENCRYPTION_KEY eksik veya 32 byte (256-bit) uzunluğunda değil.');
        }

        $ivlen = openssl_cipher_iv_length(self::CIPHER);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $tag = '';

        $ciphertext = openssl_encrypt($plaintext, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv, $tag);

        if ($ciphertext === false) {
            throw new Exception('Şifreleme başarısız oldu.');
        }

        // IV, Tag ve Ciphertext'i güvenli şekilde birleştir ve base64 yap
        return base64_encode($iv . $tag . $ciphertext);
    }

    /**
     * Şifrelenmiş (base64) veriyi çözer.
     */
    public static function decrypt(string $encryptedBase64): ?string
    {
        if (empty($encryptedBase64)) {
            return null;
        }

        $key = $_ENV['AES_ENCRYPTION_KEY'] ?? '';

        if (strlen($key) !== 32) {
            throw new Exception('AES_ENCRYPTION_KEY eksik veya 32 byte (256-bit) uzunluğunda değil.');
        }

        $data = base64_decode($encryptedBase64);

        if ($data === false) {
            return null; // Geçersiz base64
        }

        $ivlen = openssl_cipher_iv_length(self::CIPHER);

        // Verinin minimum uzunluğu (IV + Tag) kontrolü
        if (strlen($data) <= ($ivlen + 16)) {
            return null; // Veri bozuk veya eksik
        }

        $iv = substr($data, 0, $ivlen);
        $tag = substr($data, $ivlen, 16);
        $ciphertext = substr($data, $ivlen + 16);

        $plaintext = openssl_decrypt($ciphertext, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv, $tag);

        if ($plaintext === false) {
            return null; // Çözme başarısız (Yanlış key veya veri bütünlüğü bozulmuş)
        }

        return $plaintext;
    }
}