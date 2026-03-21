<?php

namespace App\Models\Concerns;

use App\Models\AuditLogModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * LogsActivity
 *
 * Attach to any Eloquent model to get automatic audit logging.
 * Writes one row to audit_logs on every create / update / delete / restore.
 *
 * Usage:
 *   class PatientModel extends Model {
 *       use LogsActivity;
 *   }
 *
 * To exclude sensitive fields from the log:
 *   protected array $auditExclude = ['password', 'token'];
 *
 * To log only specific fields:
 *   protected array $auditInclude = ['name', 'status'];
 */
trait LogsActivity
{
    protected static function bootLogsActivity(): void
    {
        static::created(fn($m)  => $m->writeLog('created',  null,       $m->getAuditAttributes()));
        static::updated(fn($m)  => $m->writeLog('updated',  $m->getOriginalAuditAttributes(), $m->getAuditAttributes()));
        static::deleted(fn($m)  => $m->writeLog('deleted',  $m->getAuditAttributes(), null));

        if (method_exists(static::class, 'restored')) {
            static::restored(fn($m) => $m->writeLog('restored', null, $m->getAuditAttributes()));
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function writeLog(string $event, ?array $old, ?array $new): void
    {
        try {
            AuditLogModel::create([
                'clinic_id'  => $this->clinic_id ?? (app()->has('currentClinic') ? app('currentClinic')->id : null),
                'user_id'    => Auth::id(),
                'model'      => class_basename(static::class),
                'model_id'   => (string) $this->getKey(),
                'event'      => $event,
                'old_values' => $old,
                'new_values' => $new,
                'ip_address' => Request::ip(),
                'user_agent' => substr(Request::userAgent() ?? '', 0, 255),
            ]);
        } catch (\Throwable) {
            // Never break the main request over an audit failure
        }
    }

    protected function getAuditAttributes(): array
    {
        $attrs = $this->getAttributes();

        if (!empty($this->auditExclude)) {
            $attrs = array_diff_key($attrs, array_flip($this->auditExclude));
        }

        if (!empty($this->auditInclude)) {
            $attrs = array_intersect_key($attrs, array_flip($this->auditInclude));
        }

        return $attrs;
    }

    protected function getOriginalAuditAttributes(): array
    {
        $attrs = $this->getOriginal();

        if (!empty($this->auditExclude)) {
            $attrs = array_diff_key($attrs, array_flip($this->auditExclude));
        }

        if (!empty($this->auditInclude)) {
            $attrs = array_intersect_key($attrs, array_flip($this->auditInclude));
        }

        return $attrs;
    }
}
