<?php
// ══════════════════════════════════════════════════════════════════════════════
// FILE: app/Models/DepartmentModel.php
// ══════════════════════════════════════════════════════════════════════════════

namespace App\Models;

use App\Models\Base\Auditable;
use App\Models\Base\ClinicScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DepartmentModel extends Model
{
    use SoftDeletes, Auditable, ClinicScope;

    protected $table = 'departments';

    protected $fillable = [
        'clinic_id', 'code', 'name', 'name_kh',
        'head_employee_id', 'phone', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(ClinicModel::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(EmployeeModel::class, 'department_id');
    }

    public function head(): BelongsTo
    {
        return $this->belongsTo(EmployeeModel::class, 'head_employee_id');
    }
}
