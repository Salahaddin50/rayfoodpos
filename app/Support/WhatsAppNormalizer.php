<?php

namespace App\Support;

class WhatsAppNormalizer
{
    /** Country codes (digits only), longest first so e.g. 994 matches before 99. */
    private const COUNTRY_CODES = ['994', '966', '971', '91', '92', '90', '86', '81', '44', '49', '39', '34', '33', '7', '1'];

    public static function normalize(?string $value): string
    {
        $v = trim((string) $value);
        if ($v === '') return '';

        $hasPlus = str_starts_with($v, '+');
        $digits = preg_replace('/\D+/', '', $v) ?? '';
        if ($digits === '') return '';

        // Fix erroneous 994 prefix: 994971503531437 → 971503531437 (e.g. user had AZ selected but typed full UAE number)
        if (str_starts_with($digits, '994') && strlen($digits) > 6) {
            $after994 = substr($digits, 3);
            foreach (self::COUNTRY_CODES as $code) {
                if ($code === '994') continue;
                if (str_starts_with($after994, $code) && strlen($after994) >= strlen($code) + 6) {
                    $digits = $after994;
                    break;
                }
            }
        }

        // Strip leading 0 after country code: +860503531437 → +86503531437, +9940503531437 → +994503531437
        foreach (self::COUNTRY_CODES as $code) {
            $len = strlen($code);
            if (str_starts_with($digits, $code . '0') && strlen($digits) > $len + 1) {
                $digits = $code . substr($digits, $len + 1);
                break;
            }
        }

        return '+' . $digits;
    }
}


