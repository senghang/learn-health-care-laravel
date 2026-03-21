<?php

use App\Common\Constants\DateFormats;
use App\Common\Utils\CodeGenerator;
use App\Common\Utils\Currency;

// ── Date formatting helpers ──────────────────────────────────────────────────
// All accept Carbon|null and return '' on null.

if (!function_exists('df_dt')) {
    /** Display datetime  →  17/03/2026 09:30 */
    function df_dt($dt): string
    {
        return $dt?->format(DateFormats::DISPLAY_DATETIME) ?? '';
    }
}

if (!function_exists('df_d')) {
    /** Display date  →  17/03/2026 */
    function df_d($dt): string
    {
        return $dt?->format(DateFormats::DISPLAY_DATE) ?? '';
    }
}

if (!function_exists('df_t')) {
    /** Display time only  →  09:30 */
    function df_t($dt): string
    {
        return $dt?->format(DateFormats::DISPLAY_TIME) ?? '';
    }
}

if (!function_exists('df_short')) {
    /** Short display (no year)  →  17/03 09:30 */
    function df_short($dt): string
    {
        return $dt?->format(DateFormats::DISPLAY_SHORT) ?? '';
    }
}

if (!function_exists('df_long')) {
    /** Human-friendly full date  →  Monday, 17 March 2026 */
    function df_long($dt): string
    {
        return $dt?->format(DateFormats::DISPLAY_LONG) ?? '';
    }
}

if (!function_exists('df_input')) {
    /** Value for <input type="date">  →  2026-03-17 */
    function df_input($dt): string
    {
        return $dt?->format(DateFormats::INPUT_DATE) ?? '';
    }
}

if (!function_exists('df_input_dt')) {
    /** Value for <input type="datetime-local">  →  2026-03-17T09:30 */
    function df_input_dt($dt): string
    {
        return $dt?->format(DateFormats::INPUT_DATETIME) ?? '';
    }
}

if (!function_exists('df_now_input')) {
    /** Current datetime as datetime-local value  →  2026-03-17T09:30 */
    function df_now_input(): string
    {
        return now()->format(DateFormats::INPUT_DATETIME);
    }
}

if (!function_exists('df_today_input')) {
    /** Today as date input value  →  2026-03-17 */
    function df_today_input(): string
    {
        return today()->format(DateFormats::INPUT_DATE);
    }
}

// ── Currency helpers ─────────────────────────────────────────────────────────

if (!function_exists('khr')) {
    /** Format as KHR  →  "30,000 KHR" */
    function khr(float|int|null $amount): string
    {
        return Currency::khr($amount);
    }
}

if (!function_exists('khr_fmt')) {
    /** Format number only (no suffix)  →  "30,000" */
    function khr_fmt(float|int|null $amount): string
    {
        return Currency::format($amount);
    }
}

if (!function_exists('khr_or_dash')) {
    /** "30,000 KHR" or "—" if empty */
    function khr_or_dash(float|int|null $amount): string
    {
        return Currency::khrOrDash($amount);
    }
}

// ── Code generation helpers ───────────────────────────────────────────────────

if (!function_exists('next_visit_code')) {
    /** Preview next visit code for display (no lock) */
    function next_visit_code(): string
    {
        return CodeGenerator::visitPreview();
    }
}
