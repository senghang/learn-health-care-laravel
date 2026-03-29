<?php

namespace App\Models\Base;

use App\Models\ClinicModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

/**
 * ClinicScope Trait — Multi-tenant data isolation via Eloquent ORM.
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * THREE LAYERS OF PROTECTION
 * ─────────────────────────────────────────────────────────────────────────────
 *
 *   Layer 1 — Global Query Scope:
 *     Every SELECT/UPDATE/DELETE gets WHERE clinic_id = ? automatically.
 *
 *   Layer 2 — Auto-fill on Create:
 *     clinic_id set from currentClinic() — developers never pass it manually.
 *
 *   Layer 3 — Explicit Named Scope:
 *     forClinic($id) for Artisan commands and queue jobs.
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * PERFORMANCE REQUIREMENT
 * ─────────────────────────────────────────────────────────────────────────────
 *
 *   Every table using this trait MUST have an index on `clinic_id`.
 *   The migration 2026_04_15_000001 adds missing indexes automatically.
 *
 *   In new migrations, always add:
 *     $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
 *     $table->index('clinic_id');  // ← REQUIRED for ClinicScope performance
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * MODELS THAT MUST USE THIS TRAIT
 * ─────────────────────────────────────────────────────────────────────────────
 *
 *   PatientModel, VisitModel, InvoiceModel, InvoiceMedicationModel,
 *   InvoiceServiceModel, PrescriptionModel, PrescriptionMedicationModel,
 *   PaymentModel, LaboratoryModel, LaboratoryResultModel, ImageryModel,
 *   ImageryResultModel, TriageModel, VitalSignModel, DiagnosisModel,
 *   MedicalHistoryModel, PhysicalExaminationModel, SoapModel,
 *   OutInPatientModel, ReferralModel, WardModel, RoomModel, BedModel,
 *   MedicineModel, ServiceModel, StockMovementModel, InventoryTransactionModel,
 *   PrintTemplateModel, AuditLogModel,
 *   EmployeeModel, SupplierModel, PharmacyDispenseModel,
 *   LabTestCatalogModel, StoreSettingModel
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * MODELS THAT MUST NOT USE THIS TRAIT
 * ─────────────────────────────────────────────────────────────────────────────
 *
 *   ProvinceModel, DistrictModel, CommuneModel, VillageModel
 *   (Shared administrative reference data)
 *
 *   RoleModel, PermissionModel
 *   (Scoped manually via clinic_id where clause in controllers,
 *    not via global scope — because permission seeding needs cross-clinic access)
 *
 *   User, SuperAdmin
 *   (Authenticated separately — not tenant-scoped)
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * BYPASSING (admin / Artisan ONLY)
 * ─────────────────────────────────────────────────────────────────────────────
 *
 *   VisitModel::withoutGlobalScope('clinic')->paginate();
 *   VisitModel::forClinic($clinicId)->get();
 *
 *   ⚠️  NEVER bypass in user-facing controllers.
 */
trait ClinicScope
{
    protected static function bootClinicScope(): void
    {
        // Layer 1: Global query scope
        static::addGlobalScope('clinic', function ($query) {
            if (!app()->has('currentClinic')) {
                if (app()->runningInConsole()) {
                    return; // Artisan — allow cross-clinic queries
                }
                Log::warning('[ClinicScope] currentClinic not bound in HTTP context', [
                    'model' => static::class,
                    'url'   => request()?->fullUrl(),
                ]);
                return;
            }

            $table = (new static)->getTable();
            $query->where("{$table}.clinic_id", app('currentClinic')->id);
        });

        // Layer 2: Auto-fill on create
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

    public function scopeForClinic($query, int $clinicId): void
    {
        $query->where($this->getTable() . '.clinic_id', $clinicId);
    }

    public function getClinicIdSafeAttribute(): ?int
    {
        return $this->clinic_id
            ?? (app()->has('currentClinic') ? app('currentClinic')->id : null);
    }
}
