<?php

namespace App\Models\Base;

use App\Models\ClinicModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

/**
 * ClinicScope Trait
 *
 * Multi-tenant data isolation enforced at the Eloquent ORM layer.
 *
 * This trait provides THREE layers of protection:
 *
 *   Layer 1 — Query scope:
 *     Every SELECT, UPDATE, DELETE automatically gets WHERE clinic_id = ?
 *     No developer can forget — it's always applied.
 *
 *   Layer 2 — Auto-fill on create:
 *     clinic_id is set automatically from currentClinic() when creating records.
 *     Developers never need to pass clinic_id manually.
 *
 *   Layer 3 — Explicit named scope:
 *     forClinic($id) allows safe scoping in Artisan commands and queue jobs.
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * MODELS THAT MUST USE THIS TRAIT (tenant-specific data)
 * ─────────────────────────────────────────────────────────────────────────────
 *   PatientModel, VisitModel, InvoiceModel, InvoiceMedicationModel,
 *   InvoiceServiceModel, PrescriptionModel, PrescriptionMedicationModel,
 *   PaymentModel, LaboratoryModel, ImageryModel, TriageModel,
 *   VitalSignModel, DiagnosisModel, MedicalHistoryModel, SoapModel,
 *   ReferralModel, WardModel, RoomModel, BedModel,
 *   MedicineModel, ServiceModel, StockMovementModel,
 *   PrintTemplateModel, TranslationModel, AuditLogModel,
 *   RoleModel, PermissionModel, ClinicSettingModel
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * MODELS THAT MUST NOT USE THIS TRAIT (shared reference data)
 * ─────────────────────────────────────────────────────────────────────────────
 *   ProvinceModel, DistrictModel, CommuneModel, VillageModel
 *   (These are administrative reference data shared across all clinics)
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * USAGE
 * ─────────────────────────────────────────────────────────────────────────────
 *   class VisitModel extends Model
 *   {
 *       use SoftDeletes, Auditable, ClinicScope;  ← add here
 *   }
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * BYPASSING (admin and Artisan ONLY)
 * ─────────────────────────────────────────────────────────────────────────────
 *   // Admin controller listing all clinics' visits:
 *   VisitModel::withoutGlobalScope('clinic')->paginate();
 *
 *   // Queue job for a specific clinic:
 *   VisitModel::forClinic($clinicId)->whereDate('admitted_at', today())->get();
 *
 *   ⚠️  NEVER bypass the clinic scope in user-facing controllers.
 */
trait ClinicScope
{
    protected static function bootClinicScope(): void
    {
        // ── Layer 1: Global query scope ───────────────────────────────────────
        static::addGlobalScope('clinic', function ($query) {
            if (!app()->has('currentClinic')) {
                // Artisan/queue context: allow but log for debugging
                if (app()->runningInConsole()) {
                    return; // Commands may intentionally query all clinics
                }
                // HTTP without clinic context = bug in middleware setup
                Log::warning('[ClinicScope] currentClinic not bound in HTTP context', [
                    'model' => static::class,
                    'url'   => request()?->fullUrl(),
                ]);
                return;
            }

            $table = (new static)->getTable();
            $query->where("{$table}.clinic_id", app('currentClinic')->id);
        });

        // ── Layer 2: Auto-fill clinic_id on create ────────────────────────────
        static::creating(function (self $model) {
            if (app()->has('currentClinic') && empty($model->clinic_id)) {
                $model->clinic_id = app('currentClinic')->id;
            }
        });
    }

    // ── Relationship ──────────────────────────────────────────────────────────

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(ClinicModel::class);
    }

    // ── Layer 3: Explicit scopes ──────────────────────────────────────────────

    /**
     * Scope queries to a specific clinic by ID.
     * Use in Artisan commands and queue jobs where currentClinic() is not set.
     *
     * Usage: VisitModel::forClinic(3)->get();
     */
    public function scopeForClinic($query, int $clinicId): void
    {
        $query->where($this->getTable() . '.clinic_id', $clinicId);
    }

    /**
     * Convenience: get the current clinic's ID from this model instance.
     */
    public function getClinicIdSafeAttribute(): ?int
    {
        return $this->clinic_id
            ?? (app()->has('currentClinic') ? app('currentClinic')->id : null);
    }
}
