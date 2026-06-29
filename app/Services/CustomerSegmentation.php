<?php

namespace App\Services;

class CustomerSegmentation
{
    public const REPEAT_ORDER_THRESHOLD = 2;

    public const LOYAL_CUSTOMER_THRESHOLD = 3;

    public function segment(int $completedBookingsCount): string
    {
        return match (true) {
            $completedBookingsCount >= self::LOYAL_CUSTOMER_THRESHOLD => 'Loyal',
            $completedBookingsCount >= self::REPEAT_ORDER_THRESHOLD => 'Repeat',
            default => 'Aktif',
        };
    }
}
