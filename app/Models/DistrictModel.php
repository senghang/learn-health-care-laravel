<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Request;

class DistrictModel extends Model
{
    use HasFactory;
    protected $table = 'districts';

    protected $fillable = [
        'code',
        'name_kh',
        'name_en',
        'latitude',
        'longtitude',
        'province',
        'postal',
        'note',
    ];

    static public function getSingle($id){
        return self::find($id);
    }

    static public function getRecord(){
        $return = self::select('districts.*')
                  ->orderBy('id', 'desc');
    
        // pageination
        if(!empty(Request::get('search'))){
            $search = Request::get('search');
            $return = $return->where(function ($q) use ($search) {
                $q->where('code', 'LIKE', "%{$search}%")
                ->orWhere('name_kh', 'LIKE', "%{$search}%")
                ->orWhere('name_en', 'LIKE', "%{$search}%")
                ->orWhere('province', 'LIKE', "%{$search}%");
            });
        }

        if(!empty(Request::get('per_page')) && Request::get('per_page') == 'all'){
            $return = $return->get();
        }else{
            $per_page = !empty(Request::get('per_page')) ? Request::get('per_page') : 25;
            $return = $return->paginate($per_page);
        }
        return $return;
    }
}
