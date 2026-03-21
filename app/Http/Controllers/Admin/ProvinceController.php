<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Imports\ProvinceImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\ProvinceModel;



class ProvinceController extends Controller
{
    //
    public function index(){
        $data['getRecord'] = ProvinceModel::getRecord();
        return view('admin.address.province.province_list',$data);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        Excel::import(new ProvinceImport, $request->file('file'));

        return back()->with('success', 'Provinces imported successfully!');
    }

    public function add(){

        return view('admin.address.province.province_add');    
    }

    public function insert(Request $request){
   
            request()->validate([
                'name_kh' => 'required',
                'name_en' => 'required',
            ]);
            $save = new ProvinceModel;
            $save->code =    trim($request->code);
            $save->name_kh = trim($request->name_kh);
            $save->name_en = trim($request->name_en);
            $save->latitude = trim($request->latitude);
            $save->longtitude = trim($request->longtitude);
            $save->note = trim($request->note);
            // dd($save);
            $save->save();
            return redirect('backend/province')->with('success', 'Province successfully added.'); 

    }
    public function edit($id){

        $data['getRecord'] = ProvinceModel::find($id);
        return view('admin.address.province.province_edit',$data);

    }

    public function update($id, Request $request){

            $save = ProvinceModel::getSingle($id);
            $save->name_kh = trim($request->name_kh);
            $save->name_en = trim($request->name_en);
            $save->latitude = trim($request->latitude);
            $save->longtitude = trim($request->longtitude);
            $save->note = trim($request->note);
            $save->save();
            
            return redirect('backend/province')->with('success', 'Province Successfully Updated.');

    }
        public function delete($id){

            $save = ProvinceModel::getSingle($id);
            $save->delete();
            return redirect('backend/province')->with('success', "Province successfully deleted");

    }
}
