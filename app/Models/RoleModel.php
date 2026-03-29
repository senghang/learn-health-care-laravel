<?php

namespace App\Models;

use App\Models\Base\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoleModel extends Model
{
    use Auditable;

    protected $table = 'roles';

    protected $fillable = ['clinic_id', 'name', 'description', 'level', 'is_system'];

    protected $casts = ['is_system' => 'boolean'];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(ClinicModel::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(PermissionModel::class, 'role_permission', 'role_id', 'permission_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }

    public function hasPermission(string $slug): bool
    {
        return $this->permissions->contains('slug', $slug);
    }
}
