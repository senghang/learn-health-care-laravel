<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\ClinicModel;

class ClinicController extends Controller
{
    public function index()
    {
        $data['getRecord'] = ClinicModel::getRecord();
        return view('admin.clinic.list.clinic_list', $data);
    }

    public function add()
    {
        return view('admin.clinic.list.clinic_add');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required',
            'subdomain' => 'required|unique:clinics',
            'logo'      => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $clinic = new ClinicModel();
        $clinic->code        = $request->code;
        $clinic->name        = $request->name;
        $clinic->name_kh     = $request->name_kh;
        $clinic->name_en     = $request->name_en;
        $clinic->subdomain   = $request->subdomain;
        $clinic->start_date  = $request->start_date;
        $clinic->end_date    = $request->end_date;
        $clinic->owner_name  = $request->owner_name;
        $clinic->owner_number = $request->owner_contact;
        $clinic->support     = $request->support;
        $clinic->note        = $request->note;
        $clinic->save(); // save first to get the ID

        if ($request->hasFile('logo')) {
            $clinic->logo = $this->uploadLogo($request, $clinic);
            $clinic->save();
        }

        return redirect('backend/clinic')->with('success', 'Clinic successfully created.');
    }

    public function edit($id)
    {
        $data['getRecord'] = ClinicModel::getRecordById($id);
        return view('admin.clinic.list.clinic_edit', $data);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'      => 'required',
            'subdomain' => 'required|unique:clinics,subdomain,' . $id,
            'logo'      => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $clinic = ClinicModel::findOrFail($id);
        $clinic->code        = $request->code;
        $clinic->name        = $request->name;
        $clinic->name_kh     = $request->name_kh;
        $clinic->name_en     = $request->name_en;
        $clinic->subdomain   = $request->subdomain;
        $clinic->start_date  = $request->start_date;
        $clinic->end_date    = $request->end_date;
        $clinic->owner_name  = $request->owner_name;
        $clinic->owner_number = $request->owner_contact;
        $clinic->support     = $request->support;
        $clinic->note        = $request->note;

        if ($request->hasFile('logo')) {
            // Delete old logo if it exists
            if ($clinic->logo && Storage::disk('public')->exists($clinic->logo)) {
                Storage::disk('public')->delete($clinic->logo);
            }
            $clinic->logo = $this->uploadLogo($request, $clinic);
        }

        $clinic->save();

        return redirect('backend/clinic')->with('success', 'Clinic successfully updated.');
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Upload logo to the PUBLIC disk (accessible via asset('storage/...')).
     *
     * FIXED: Was saving to the private disk but displaying with asset('storage/...')
     * which reads from the public disk. Now consistent: always public disk.
     *
     * Returns the relative path stored in the DB, e.g. "clinics/1_logo.jpg"
     */
    private function uploadLogo(Request $request, ClinicModel $clinic): string
    {
        $filename = $clinic->id . '_logo.jpg';
        $folder   = 'clinics';

        return $request->file('logo')->storeAs($folder, $filename, 'public');
    }
}
