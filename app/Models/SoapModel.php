<?php

namespace App\Models;

use App\Models\Base\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SoapModel extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'soaps';
    
    protected $fillable = ['encounter_code', 'subjective', 'objective', 'assessment', 'evaluation', 'plan'];
}
