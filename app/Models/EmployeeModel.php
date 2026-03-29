<?php

namespace App\Models;

use App\Models\Base\Auditable;
use App\Models\Base\ClinicScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeModel extends Model
{
    use SoftDeletes, Auditable, ClinicScope;

    protected $table = 'employees';

    protected $fillable = [
        'clinic_id', 'code', 'user_id', 'department_id',
        'surname', 'name', 'name_kh', 'gender', 'birthdate',
        'phone', 'email', 'employee_type', 'specialization',
        'license_number', 'hire_date', 'end_date', 'status', 'photo_path',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'hire_date' => 'date',
        'end_date'  => 'date',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function clinic(): BelongsTo { return $this->belongsTo(ClinicModel::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function department(): BelongsTo { return $this->belongsTo(DepartmentModel::class, 'department_id'); }

    // ── Computed ──────────────────────────────────────────────────────────────

    public function getFullNameAttribute(): string { return "{$this->surname} {$this->name}"; }
    public function getDisplayNameAttribute(): string
    {
        return app()->getLocale() === 'km' && $this->name_kh ? $this->name_kh : $this->full_name;
    }
}
