<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VillageModel;

use App\Imports\VillageImport;
use Maatwebsite\Excel\Facades\Excel;

class VillageController extends Controller
{
    public function index(){

        $data['getRecord'] = VillageModel::getRecord();
        return view('admin.address.village.village_list',$data);

    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        Excel::import(new VillageImport, $request->file('file'));

        return back()->with('success', 'Villages imported successfully!');
    }
}
