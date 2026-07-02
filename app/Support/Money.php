<?php

namespace App\Support;

class Money
{
    public const SYMBOL = '₹';

    public static function format(float|int|string|null $amount, int $decimals = 2): string
    {
        return self::SYMBOL.number_format((float) ($amount ?? 0), $decimals);
    }
}
