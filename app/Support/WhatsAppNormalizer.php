<?php

namespace App\Support;

class WhatsAppNormalizer
{
    public static function normalize(?string $value): string
    {
        $v = trim((string) $value);
        if ($v === '') return '';

        // Keep leading + (if present), remove everything else non-digit.
        $hasPlus = str_starts_with($v, '+');
        $digits = preg_replace('/\D+/', '', $v) ?? '';
        if ($digits === '') return '';

        $normalized = ($hasPlus ? '+' : '') . $digits;

        // Azerbaijan common input: +994 0XX.... -> +994XX....
        if (str_starts_with($normalized, '+9940')) {
            $normalized = '+994' . substr($normalized, 5);
        }
        if (str_starts_with($normalized, '9940')) {
            $normalized = '+994' . substr($normalized, 4);
        }
        if (str_starts_with($normalized, '994') && !str_starts_with($normalized, '+')) {
            $normalized = '+' . $normalized;
        }

        return $normalized;
    }
}


