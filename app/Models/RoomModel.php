<?php

namespace App\Models;

use App\Models\Base\Auditable;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomModel extends Model
{
    use SoftDeletes, Auditable, LogsActivity;

    protected $table = 'rooms';

    protected $fillable = [
        'ward_id', 'code', 'name', 'type', 'floor', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean', 'floor' => 'integer'];

    public function ward(): BelongsTo
    {
        return $this->belongsTo(WardModel::class, 'ward_id');
    }

    public function beds(): HasMany
    {
        return $this->hasMany(BedModel::class, 'room_id');
    }
}
