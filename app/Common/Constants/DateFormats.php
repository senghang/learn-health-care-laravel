<?php

namespace App\Common\Constants;

/**
 * DateFormats — single source of truth for every date/time format string.
 *
 * Usage:
 *   $dt->format(DateFormats::DISPLAY_DATETIME)   // "17/03/2026 09:30"
 *   $dt->format(DateFormats::INPUT_DATE)         // "2026-03-17"  ← HTML <input type="date">
 *   $dt->format(DateFormats::INPUT_DATETIME)     // "2026-03-17T09:30" ← datetime-local
 *
 * In Blade:
 *   {{ $visit->admitted_at?->format(\App\Common\Constants\DateFormats::DISPLAY_DATETIME) }}
 *   — or use the df_*() helpers for even shorter syntax —
 *   {{ df_dt($visit->admitted_at) }}
 */
final class DateFormats
{
    // ── HTML input values ─────────────────────────────────────────────────

    /** <input type="date"> value  →  2026-03-17 */
    public const INPUT_DATE = 'Y-m-d';

    /** <input type="datetime-local"> value  →  2026-03-17T09:30 */
    public const INPUT_DATETIME = 'Y-m-d\TH:i';

    // ── Display in UI ─────────────────────────────────────────────────────

    /** Full date + time  →  17/03/2026 09:30 */
    public const DISPLAY_DATETIME = 'd/m/Y H:i';

    /** Date only  →  17/03/2026 */
    public const DISPLAY_DATE = 'd/m/Y';

    /** Short date + time (no year)  →  17/03 09:30 */
    public const DISPLAY_SHORT = 'd/m H:i';

    /** Time only  →  09:30 */
    public const DISPLAY_TIME = 'H:i';

    /** Clock widget (date · time)  →  17/03/2026 · 09:30 */
    public const DISPLAY_CLOCK = 'd/m/Y';

    /** Day + month only  →  17/03 */
    public const DISPLAY_DAY_MONTH = 'd/m';

    /** Human-friendly full date  →  Monday, 17 March 2026 */
    public const DISPLAY_LONG = 'l, d F Y';

    // ── Export / machine-readable ─────────────────────────────────────────

    /** CSV / filename date  →  2026-03-17 */
    public const EXPORT_DATE = 'Y-m-d';

    /** CSV datetime  →  2026-03-17 09:30 */
    public const EXPORT_DATETIME = 'Y-m-d H:i';

    /** Visit code date segment  →  20260317 */
    public const CODE_DATE = 'Ymd';

    // Prevent instantiation
    private function __construct() {}
}
