<?php

namespace App\Common\Utils;

/**
 * Currency — KHR formatting and payment type helpers.
 *
 * Usage:
 *   Currency::format(30000)         // "30,000"
 *   Currency::khr(30000)            // "30,000 KHR"
 *   Currency::paymentLabel('HEF')   // "HEF — មូលនិធិ"
 */
final class Currency
{
    /** All valid payment types */
    public const TYPES = ['HEF', 'NSSF', 'CASH'];

    /** Payment type → bilingual display label */
    public const TYPE_LABELS = [
        'HEF'  => 'HEF — មូលនិធិ',
        'NSSF' => 'NSSF — ប.ស.ស',
        'CASH' => 'CASH',
    ];

    // ── Formatting ─────────────────────────────────────────────────────────

    /**
     * Format a KHR amount with thousands separator.
     * Returns "0" for null/zero rather than crashing.
     *
     * Currency::format(30000)   →  "30,000"
     */
    public static function format(float|int|null $amount): string
    {
        return number_format((float) ($amount ?? 0), 0, '.', ',');
    }

    /**
     * Format with currency suffix.
     *
     * Currency::khr(30000)   →  "30,000 KHR"
     */
    public static function khr(float|int|null $amount): string
    {
        return self::format($amount) . ' KHR';
    }

    /**
     * Format as zero if empty, with KHR.
     * Safe for invoice totals that might be null.
     */
    public static function khrOrDash(float|int|null $amount): string
    {
        return $amount ? self::khr($amount) : '—';
    }

    // ── Payment type helpers ───────────────────────────────────────────────

    /**
     * Human-readable label for a payment type code.
     *
     * Currency::paymentLabel('HEF')   →  "HEF — មូលនិធិ"
     * Currency::paymentLabel('CASH')  →  "CASH"
     * Currency::paymentLabel('XYZ')   →  "XYZ"   (unknown → passthrough)
     */
    public static function paymentLabel(string|null $type): string
    {
        return self::TYPE_LABELS[$type ?? ''] ?? ($type ?? '—');
    }

    /**
     * Options array suitable for <x-form.select :options="...">.
     *
     * ['HEF' => 'HEF — មូលនិធិ', 'NSSF' => 'NSSF — ប.ស.ស', 'CASH' => 'CASH']
     */
    public static function typeOptions(): array
    {
        return self::TYPE_LABELS;
    }

    // Prevent instantiation
    private function __construct() {}
}
