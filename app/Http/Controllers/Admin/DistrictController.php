<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DistrictModel;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DistrictImport;


class DistrictController extends Controller
{
    public function index(){
        $data['getRecord'] = DistrictModel::getRecord(); // Fetch district records here
        return view('admin.address.district.district_list',$data);
    }
    
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        Excel::import(new DistrictImport, $request->file('file'));

        return back()->with('success', 'District imported successfully!');
    }

    public function add(){

        return view('admin.address.district.district_add');

    }

    
    public function edit( $id){

        $data['getRecord'] = DistrictModel::find($id);

        return view('admin.address.district.district_edit',$data);

    }

    public function update($id, Request $request){

            $save = DistrictModel::getSingle($id);
            $save->name_kh = trim($request->name_kh);
            $save->name_en = trim($request->name_en);
            $save->latitude = trim($request->latitude);
            $save->longtitude = trim($request->longtitude);
            $save->province = trim($request->province);
            $save->postal = trim($request->postal);
            $save->note = trim($request->note);
            $save->save();
            
            return redirect('backend/district')->with('success', 'District Successfully Updated.');
            

    }
}
