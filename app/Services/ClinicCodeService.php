<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * ClinicCodeService
 *
 * Concurrency-safe, clinic-scoped unique code generation for PostgreSQL.
 *
 * ═══════════════════════════════════════════════════════════════════════════
 * ROOT CAUSE OF THE BUG
 * ═══════════════════════════════════════════════════════════════════════════
 *
 * OLD CODE (CodeGenerator.php):
 *
 *   DB::transaction(function () {
 *       $count = VisitModel::whereDate('created_at', today())
 *           ->lockForUpdate()   ← ❌ INVALID
 *           ->count();          ← ❌ AGGREGATE
 *   });
 *
 * PostgreSQL ERROR: "FOR UPDATE is not allowed with aggregate functions"
 *
 * Root cause: PostgreSQL does not allow SELECT ... FOR UPDATE when the query
 * contains aggregate functions (COUNT, SUM, MAX, etc.). This is a fundamental
 * PostgreSQL restriction, unlike MySQL which allows it.
 *
 * Additionally, even without the error, the old approach had a race condition:
 * two concurrent requests could both read count=5 before either writes,
 * causing duplicate codes: V202603220006 generated twice.
 *
 * ═══════════════════════════════════════════════════════════════════════════
 * THE FIX: Atomic upsert with RETURNING
 * ═══════════════════════════════════════════════════════════════════════════
 *
 * PostgreSQL's INSERT ... ON CONFLICT DO UPDATE ... RETURNING is atomic:
 *   - If no row exists → inserts with last_seq = 1, returns 1
 *   - If row exists → increments last_seq by 1, returns new value
 *   - Two concurrent transactions cannot both get the same number
 *   - No SELECT needed → no aggregate → no FOR UPDATE error
 *
 * ═══════════════════════════════════════════════════════════════════════════
 * CODE FORMAT
 * ═══════════════════════════════════════════════════════════════════════════
 *
 * {PREFIX}{YYYYMMDD}{NNNN}
 *
 * Examples:
 *   V202603220001    → Visit #1 on 2026-03-22 for clinic
 *   PT202603220001   → Patient #1 on 2026-03-22 for clinic
 *   INV202603220001  → Invoice #1 on 2026-03-22 for clinic
 *
 * Sequence resets daily (one row per clinic+prefix+date).
 * Each clinic has independent sequences — no global counter.
 */
final class ClinicCodeService
{
    /**
     * Generate the next unique code for a clinic+prefix+date combination.
     *
     * Thread-safe via PostgreSQL atomic upsert.
     * Resets to 0001 each day.
     *
     * @param  int    $clinicId  The owning clinic's ID
     * @param  string $prefix    Uppercase prefix: 'V', 'PT', 'INV', 'RX', 'PAY'
     * @param  int    $pad       Zero-padding width (default 4 → 0001–9999)
     * @return string            e.g. "V202603220001"
     */
    public static function next(int $clinicId, string $prefix, int $pad = 4): string
    {
        $prefix = strtoupper(trim($prefix));
        $today  = now()->toDateString();  // 'YYYY-MM-DD' — DB date format

        /**
         * Atomic PostgreSQL upsert:
         *
         * First request of the day for clinic=1, prefix='V':
         *   → INSERT (clinic_id=1, prefix='V', seq_date=today, last_seq=1)
         *   → RETURNING last_seq = 1
         *   → Code: V202603220001
         *
         * Second concurrent request (same clinic, prefix, date):
         *   → ON CONFLICT: UPDATE last_seq = 1 + 1 = 2
         *   → RETURNING last_seq = 2
         *   → Code: V202603220002
         *
         * No two requests ever get the same number.
         */
        $row = DB::selectOne("
            INSERT INTO clinic_code_sequences
                (clinic_id, prefix, seq_date, last_seq, updated_at)
            VALUES
                (:clinic_id, :prefix, :seq_date, 1, NOW())
            ON CONFLICT (clinic_id, prefix, seq_date)
            DO UPDATE SET
                last_seq   = clinic_code_sequences.last_seq + 1,
                updated_at = NOW()
            RETURNING last_seq
        ", [
            'clinic_id' => $clinicId,
            'prefix'    => $prefix,
            'seq_date'  => $today,
        ]);

        $seq = (int) $row->last_seq;

        return $prefix . now()->format('Ymd') . str_pad($seq, $pad, '0', STR_PAD_LEFT);
    }

    /**
     * Preview the NEXT code without consuming a sequence number.
     *
     * ⚠️  NOT concurrency-safe — for display only (e.g. "New Visit" form header).
     * The actual code assigned at save time may differ if another request
     * saves between the preview and the save.
     */
    public static function preview(int $clinicId, string $prefix, int $pad = 4): string
    {
        $prefix = strtoupper(trim($prefix));

        $row = DB::selectOne("
            SELECT last_seq
            FROM clinic_code_sequences
            WHERE clinic_id = :clinic_id
              AND prefix    = :prefix
              AND seq_date  = :seq_date
        ", [
            'clinic_id' => $clinicId,
            'prefix'    => $prefix,
            'seq_date'  => now()->toDateString(),
        ]);

        $next = $row ? ($row->last_seq + 1) : 1;

        return $prefix . now()->format('Ymd') . str_pad($next, $pad, '0', STR_PAD_LEFT);
    }

    // ── Typed convenience methods ─────────────────────────────────────────────

    public static function visit(int $clinicId): string         { return self::next($clinicId, 'V');    }
    public static function patient(int $clinicId): string       { return self::next($clinicId, 'PT');   }
    public static function invoice(int $clinicId): string       { return self::next($clinicId, 'INV');  }
    public static function prescription(int $clinicId): string  { return self::next($clinicId, 'RX');   }
    public static function payment(int $clinicId): string       { return self::next($clinicId, 'PAY');  }
    public static function labRequest(int $clinicId): string    { return self::next($clinicId, 'LAB');  }
    public static function stockMovement(int $clinicId): string { return self::next($clinicId, 'STK');  }

    public static function visitPreview(int $clinicId): string  { return self::preview($clinicId, 'V');  }
    public static function patientPreview(int $clinicId): string{ return self::preview($clinicId, 'PT'); }

    // ── Non-sequential codes (deterministic, no sequence table) ──────────────

    /** OPD-V202603220001 or IPD-V202603220001 — one per visit, deterministic */
    public static function encounter(string $visitCode, string $visitType): string
    {
        return strtoupper($visitType) . '-' . $visitCode;
    }

    /** TR-V202603220001 — one triage per visit */
    public static function triage(string $visitCode): string
    {
        return 'TR-' . $visitCode;
    }

    /** VS-V202603220001-1748293847123 — multiple vitals per visit, needs timestamp */
    public static function vitalSign(string $visitCode): string
    {
        return 'VS-' . $visitCode . '-' . (int)(microtime(true) * 1000);
    }

    /** REF-V202603220001-1748293847 */
    public static function referral(string $visitCode): string
    {
        return 'REF-' . $visitCode . '-' . now()->timestamp;
    }

    private function __construct() {} // Static-only class, cannot be instantiated
}
