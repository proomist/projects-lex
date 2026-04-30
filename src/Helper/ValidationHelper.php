<?php

declare(strict_types=1);

namespace App\Helper;

class ValidationHelper
{
    /**
     * TC Kimlik No doğrulama (11 haneli checksum algoritması).
     * Kurallar:
     * - 11 hane, tamamı rakam
     * - İlk hane 0 olamaz
     * - ((d1+d3+d5+d7+d9)*7 - (d2+d4+d6+d8)) mod 10 = d10
     * - (d1+d2+...+d10) mod 10 = d11
     */
    public static function validateTcKimlikNo(string $tc): bool
    {
        if (strlen($tc) !== 11) return false;
        if (!ctype_digit($tc)) return false;
        if ($tc[0] === '0') return false;

        $digits = array_map('intval', str_split($tc));

        // 10. hane kontrolü
        $oddSum = $digits[0] + $digits[2] + $digits[4] + $digits[6] + $digits[8];
        $evenSum = $digits[1] + $digits[3] + $digits[5] + $digits[7];
        $check10 = (($oddSum * 7) - $evenSum) % 10;
        if ($check10 < 0) $check10 += 10;
        if ($check10 !== $digits[9]) return false;

        // 11. hane kontrolü
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += $digits[$i];
        }
        if ($sum % 10 !== $digits[10]) return false;

        return true;
    }

    /**
     * Vergi Kimlik Numarası (VKN) doğrulama (10 haneli modüler aritmetik).
     * Kurallar:
     * - 10 hane, tamamı rakam
     * - Her basamak için modüler işlem yapılır, 10. hane kontrol basamağıdır
     */
    public static function validateVkn(string $vkn): bool
    {
        if (strlen($vkn) !== 10) return false;
        if (!ctype_digit($vkn)) return false;

        $digits = array_map('intval', str_split($vkn));
        $total = 0;

        for ($i = 0; $i < 9; $i++) {
            $tmp = ($digits[$i] + (9 - $i)) % 10;
            $power = 1 << (9 - $i); // 2^(9-i)
            $tmp = ($tmp * $power) % 9;
            if ($tmp === 0 && (9 - $i) !== 0) {
                $tmp = 9;
            }
            $total += $tmp;
        }

        $checkDigit = (10 - ($total % 10)) % 10;

        return $checkDigit === $digits[9];
    }
}
