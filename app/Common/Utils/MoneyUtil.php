<?php

namespace App\Common\Utils;

/**
 * MoneyUtil — Currency formatting for KHR / USD.
 * All amounts stored as KHR integers. USD displayed at ~4,100 rate.
 */
final class MoneyUtil
{
    public const RATE_USD = 4100;

    /** Format integer KHR → "30,000 KHR" */
    public static function khr(float|int $amount): string
    {
        return number_format($amount, 0, '.', ',') . ' KHR';
    }

    /** Format as USD */
    public static function usd(float|int $amountKhr): string
    {
        return '$' . number_format($amountKhr / self::RATE_USD, 2);
    }

    /** Short label: 30000 → "30K" */
    public static function short(float|int $n): string
    {
        if ($n >= 1_000_000) return number_format($n / 1_000_000, 1) . 'M';
        if ($n >= 1_000)     return number_format($n / 1_000, 0) . 'K';
        return (string) $n;
    }

    /** Parse a string that may contain commas */
    public static function parse(string $s): float
    {
        return (float) str_replace(',', '', $s);
    }

    private function __construct() {}
}
