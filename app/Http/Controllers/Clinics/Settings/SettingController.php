<?php

namespace App\Http\Controllers\Clinics\Settings;

use App\Http\Controllers\Controller;
use App\Models\ClinicSettingModel;
use App\Models\MedicineModel;
use App\Models\PrintTemplateModel;
use App\Models\ServiceModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    private int $clinicId;

    public function __construct()
    {
        $this->clinicId = currentClinic()->id;
    }

    // ── General settings ──────────────────────────────────────────────────────

    public function general(): View
    {
        $clinic   = currentClinic();
        $settings = ClinicSettingModel::where('clinic_id', $this->clinicId)
            ->get()->keyBy('key');

        return view('clinics.settings.general', compact('clinic', 'settings'));
    }

    public function updateGeneral(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'clinic_name'    => 'required|string|max:120',
            'clinic_name_kh' => 'nullable|string|max:120',
            'clinic_phone'   => 'nullable|string|max:30',
            'clinic_email'   => 'nullable|email|max:120',
            'clinic_address' => 'nullable|string|max:500',
            'default_locale' => 'required|in:km,en',
            'timezone'       => 'nullable|string|max:60',
            'currency'       => 'nullable|string|max:10',
            'header_logo'    => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        // Update core clinic fields
        currentClinic()->update([
            'name'           => $data['clinic_name'],
            'name_kh'        => $data['clinic_name_kh'] ?? null,
            'default_locale' => $data['default_locale'],
        ]);

        // Store flexible settings
        $settingKeys = ['clinic_phone', 'clinic_email', 'clinic_address', 'timezone', 'currency'];
        foreach ($settingKeys as $key) {
            if (isset($data[$key])) {
                ClinicSettingModel::set($this->clinicId, $key, $data[$key]);
            }
        }

        // Upload logo
        if ($request->hasFile('header_logo')) {
            $path = $request->file('header_logo')
                ->store("clinic_{$this->clinicId}/branding", 'public');

            currentClinic()->update(['logo' => $path]);
            ClinicSettingModel::set($this->clinicId, 'header_logo', $path);
        }

        return back()->with('flash', 'ការកំណត់ត្រូវបានរក្សាទុក / Settings saved.');
    }

    // ── Print templates ───────────────────────────────────────────────────────

    public function templates(): View
    {
        $templates = PrintTemplateModel::where('clinic_id', $this->clinicId)
            ->orderBy('type')->orderBy('locale')
            ->get();

        return view('clinics.settings.templates', compact('templates'));
    }

    public function templateCreate(): View
    {
        return view('clinics.settings.template-form', [
            'template' => null,
            'types'    => PrintTemplateModel::TYPES,
        ]);
    }

    public function templateStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            // code is auto-generated
            'type'        => 'required|in:' . implode(',', PrintTemplateModel::TYPES),
            'name'        => 'required|string|max:120',
            'locale'      => 'required|in:km,en,all',
            'content'     => 'required|string',
            'paper_size'  => 'required|in:A4,A5,Letter',
            'orientation' => 'required|in:portrait,landscape',
            'is_default'  => 'boolean',
        ]);

        PrintTemplateModel::create(array_merge($data, [
            'clinic_id' => $this->clinicId,
            'is_active' => true,
        ]));

        return redirect()->route('settings.templates')
            ->with('flash', 'Template created.');
    }

    public function templateEdit(int $id): View
    {
        $template = PrintTemplateModel::where('clinic_id', $this->clinicId)->findOrFail($id);

        return view('clinics.settings.template-form', [
            'template' => $template,
            'types'    => PrintTemplateModel::TYPES,
        ]);
    }

    public function templateUpdate(Request $request, int $id): RedirectResponse
    {
        $template = PrintTemplateModel::where('clinic_id', $this->clinicId)->findOrFail($id);

        $template->update($request->validate([
            'name'        => 'required|string|max:120',
            'locale'      => 'required|in:km,en,all',
            'content'     => 'required|string',
            'paper_size'  => 'required|in:A4,A5,Letter',
            'orientation' => 'required|in:portrait,landscape',
            'is_default'  => 'boolean',
            'is_active'   => 'boolean',
        ]));

        return redirect()->route('settings.templates')
            ->with('flash', 'Template updated.');
    }

    // ── Services master ───────────────────────────────────────────────────────

    public function services(): View
    {
        $services = ServiceModel::where('clinic_id', $this->clinicId)
            ->orderBy('category')->orderBy('name')
            ->paginate(30);

        return view('clinics.settings.services', compact('services'));
    }

    public function serviceStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            // code is auto-generated
            'name'     => 'required|string|max:120',
            'name_kh'  => 'nullable|string|max:120',
            'name_en'  => 'nullable|string|max:120',
            'category' => 'nullable|string|max:60',
            'price'    => 'required|numeric|min:0',
        ]);

        ServiceModel::create(array_merge($data, [
            'clinic_id' => $this->clinicId,
            'code'      => \App\Services\ClinicCodeService::next($this->clinicId, 'SRV'),
        ]));

        return back()->with('flash', 'Service added.');
    }

    public function serviceUpdate(Request $request, int $id): RedirectResponse
    {
        $service = ServiceModel::where('clinic_id', $this->clinicId)->findOrFail($id);

        $service->update($request->validate([
            'name'      => 'required|string|max:120',
            'name_kh'   => 'nullable|string|max:120',
            'name_en'   => 'nullable|string|max:120',
            'category'  => 'nullable|string|max:60',
            'price'     => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]));

        return back()->with('flash', 'Service updated.');
    }

    // ── Medicines master ──────────────────────────────────────────────────────

    public function medicines(): View
    {
        $medicines = MedicineModel::where('clinic_id', $this->clinicId)
            ->orderBy('form')->orderBy('name')
            ->paginate(30);

        $lowStock = MedicineModel::where('clinic_id', $this->clinicId)
            ->whereColumn('stock', '<=', 'stock_alert')
            ->where('is_active', true)
            ->count();

        return view('clinics.settings.medicines', compact('medicines', 'lowStock'));
    }

    public function medicineStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            // code is auto-generated
            'name'         => 'required|string|max:120',
            'name_kh'      => 'nullable|string|max:120',
            'name_en'      => 'nullable|string|max:120',
            'generic_name' => 'nullable|string|max:120',
            'form'         => 'nullable|string|max:60',
            'strength'     => 'nullable|string|max:60',
            'unit'         => 'nullable|string|max:40',
            'price'        => 'required|numeric|min:0',
            'stock'        => 'required|integer|min:0',
            'stock_alert'  => 'required|integer|min:0',
        ]);

        MedicineModel::create(array_merge($data, [
            'clinic_id' => $this->clinicId,
            'code'      => \App\Services\ClinicCodeService::next($this->clinicId, 'MED'),
        ]));

        return back()->with('flash', 'Medicine added.');
    }

    public function medicineUpdate(Request $request, int $id): RedirectResponse
    {
        $med = MedicineModel::where('clinic_id', $this->clinicId)->findOrFail($id);

        $med->update($request->validate([
            'name'         => 'required|string|max:120',
            'name_kh'      => 'nullable|string|max:120',
            'generic_name' => 'nullable|string|max:120',
            'form'         => 'nullable|string|max:60',
            'strength'     => 'nullable|string|max:60',
            'price'        => 'required|numeric|min:0',
            'stock'        => 'required|integer|min:0',
            'stock_alert'  => 'required|integer|min:0',
            'is_active'    => 'boolean',
        ]));

        return back()->with('flash', 'Medicine updated.');
    }
}
