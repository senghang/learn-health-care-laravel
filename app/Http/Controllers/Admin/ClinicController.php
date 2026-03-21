<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClinicModel;


class ClinicController extends Controller
{
    public function index(){
        $data['getRecord'] = ClinicModel::getRecord();
        return view('admin.clinic.list.clinic_list',$data);
    }

    public function add(){
        
        return view('admin.clinic.list.clinic_add');
    }

    public function store(Request $request){

        // dd($request->all());

        $request->validate([
            'name' => 'required',
            'subdomain' => 'required|unique:clinics',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png'
        ]);
        $clinic = new ClinicModel();

        $clinic->code = $request->code;
        $clinic->name = $request->name;
        $clinic->name_kh = $request->name_kh;
        $clinic->name_en = $request->name_en;
        $clinic->subdomain = $request->subdomain;
        $clinic->start_date = $request->start_date;
        $clinic->end_date = $request->end_date;
        $clinic->owner_name = $request->owner_name;
        $clinic->owner_number = $request->owner_contact;
        $clinic->note = $request->note;
        

        // upload logo
        if ($request->hasFile('logo')) {

            $logoName = $clinic->id . '_logo.jpg';

            $path = $request->file('logo')->storeAs(
                $clinic->id . "_" . $clinic->name,   // folder name
                $logoName,
                'public'
            );

            // Save logo path to database
            $clinic->logo = $path;
           
        }
        $clinic->save();

        return redirect('backend/clinic')->with('success', 'Clinic successfully created.'); 

    }

    public function edit($id){
        $data['getRecord'] = ClinicModel::getRecordById($id);
        return view('admin.clinic.list.clinic_edit',$data);
    }

    public function update(Request $request, $id){
        $request->validate([
            'name' => 'required',
            'subdomain' => 'required|unique:clinics,subdomain,' . $id,
            'logo' => 'nullable|image|mimes:jpg,jpeg,png'
        ]);

        $clinic = ClinicModel::find($id);

        $clinic->code = $request->code;
        $clinic->name = $request->name;
        $clinic->name_kh = $request->name_kh;
        $clinic->name_en = $request->name_en;
        $clinic->subdomain = $request->subdomain;
        $clinic->start_date = $request->start_date;
        $clinic->end_date = $request->end_date;
        $clinic->owner_name = $request->owner_name;
        $clinic->owner_number = $request->owner_contact;
        $clinic->note = $request->note;

         // upload logo
         if ($request->hasFile('logo')) {

            $logoName = $clinic->id . '_logo.jpg';

            $path = $request->file('logo')->storeAs(
                $clinic->id . "_" . $clinic->name,   // folder name
                $logoName,
                'public'
            );

            // Save logo path to database
            $clinic->logo = $path;
           
        }
        
        $clinic->save();

        return redirect('backend/clinic')->with('success', 'Clinic successfully updated.'); 
    }
    
}
