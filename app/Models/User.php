<?php

namespace App\Models;

use App\Models\Base\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, Auditable;

    protected $with = ['roles'];

    protected $fillable = [
        'clinic_id', 'employee_id',
        'name', 'email', 'password', 'phone',
        'is_active', 'avatar_path',
        'last_login_at', 'last_login_ip',
    ];

    protected $hidden = ['password', 'remember_token'];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(ClinicModel::class);
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(RoleModel::class, 'user_roles', 'user_id', 'role_id')
                    ->withPivot('assigned_at', 'assigned_by');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(EmployeeModel::class);
    }

    public function can($ability, $arguments = []): bool
    {
        // Check slug-based permissions first
        if (is_string($ability) && str_contains($ability, '.')) {
            return $this->hasPermission($ability);
        }

        return parent::can($ability, $arguments);
    }

    // ── Permission check ──────────────────────────────────────────────────────

    public function hasPermission(string $slug): bool
    {
        return $this->roles->contains(fn($role) => $role->hasPermission($slug));
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }
}
