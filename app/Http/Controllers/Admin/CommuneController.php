<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\CommuneModel;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CommuneImport;

class CommuneController extends Controller
{
    public function index(Request $request){

        $data['getRecord'] = CommuneModel::getRecord(); // Fetch district records here
        return view('admin.address.commune.commune_list',$data);

    }

    
    public function import(Request $request){
        $request->validate([
            'file' => 'required|mimes:xls,xlsx'
        ]);

        $file = $request->file('file');

        // Import the data using Laravel Excel
        Excel::import(new CommuneImport, $file);

        return redirect()->back()->with('success', 'Communes imported successfully.');
    } 

    public function add(){
        
    }
}
