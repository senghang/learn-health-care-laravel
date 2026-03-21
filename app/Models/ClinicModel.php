<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClinicModel extends Model
{
    use HasFactory;

    protected $table = 'clinics';


    static public function getRecord(){
        $return = self::select('clinics.*')
                  ->orderBy('id', 'desc');

        // pageination
        if(!empty(Request::get('search'))){
            $search = Request::get('search');
            $return = $return->where(function ($q) use ($search) {
                $q->where('code', 'LIKE', "%{$search}%")
                ->orWhere('name_kh', 'LIKE', "%{$search}%")
                ->orWhere('name_en', 'LIKE', "%{$search}%")
                ->orWhere('owner_name', 'LIKE', "%{$search}%");
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

    static public function getRecordById($id){
        return self::where('id',$id)->first();
    }

}
