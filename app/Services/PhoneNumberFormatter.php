<?php

namespace App\Services;

class PhoneNumberFormatter
{
    public static function toIndonesianMobile(?string $phone): ?string
    {
        if (blank($phone)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        if (str_starts_with($digits, '8')) {
            return '62'.$digits;
        }

        return $digits;
    }

    public static function isIndonesianMobile(?string $phone): bool
    {
        return is_string($phone) && preg_match('/^628[0-9]{8,12}$/', $phone) === 1;
    }
}
