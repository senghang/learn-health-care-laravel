<?php

namespace App\Http\Controllers\Clinics\Settings;

use App\Http\Controllers\Controller;
use App\Models\StoreSettingModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreSettingController extends Controller
{
    private int $clinicId;

    public function __construct()
    {
        $this->clinicId = currentClinic()->id;
    }

    /**
     * List all store settings grouped by group.
     */
    public function index(Request $request): View
    {
        $search = $request->get('search');
        $filterGroup = $request->get('group');

        $query = StoreSettingModel::withoutGlobalScope('clinic')
            ->where(fn($q) => $q->where('clinic_id', $this->clinicId)->orWhereNull('clinic_id'))
            ->orderBy('group')
            ->orderBy('sort_order');

        if ($search) {
            $query->where(fn($q) => $q
                ->where('key', 'LIKE', "%{$search}%")
                ->orWhere('label_en', 'LIKE', "%{$search}%")
                ->orWhere('label_km', 'LIKE', "%{$search}%")
            );
        }

        if ($filterGroup) {
            $query->where('group', $filterGroup);
        }

        $settings = $query->get()->groupBy('group');

        $groups = StoreSettingModel::withoutGlobalScope('clinic')
            ->where(fn($q) => $q->where('clinic_id', $this->clinicId)->orWhereNull('clinic_id'))
            ->distinct()
            ->pluck('group')
            ->sort()
            ->values();

        return view('clinics.settings.store-settings', compact('settings', 'groups', 'search', 'filterGroup'));
    }

    /**
     * Store a new setting (clinic-specific override or new entry).
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'group'      => 'required|string|max:60',
            'key'        => 'required|string|max:80',
            'label_en'   => 'nullable|string|max:120',
            'label_km'   => 'nullable|string|max:120',
            'value'      => 'nullable|string|max:255',
            'color'      => 'nullable|string|max:20',
            'icon'       => 'nullable|string|max:40',
            'sort_order' => 'nullable|integer|min:0',
            'is_active'  => 'nullable|boolean',
        ]);

        StoreSettingModel::create(array_merge($data, [
            'clinic_id'  => $this->clinicId,
            'is_active'  => $data['is_active'] ?? true,
            'is_system'  => false,
            'sort_order' => $data['sort_order'] ?? 0,
        ]));

        return back()->with('flash', __('app.save') . ' ✓');
    }

    /**
     * Update an existing setting.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $setting = StoreSettingModel::withoutGlobalScope('clinic')->findOrFail($id);

        // If it's a system setting (clinic_id=null), create a clinic override instead
        if ($setting->clinic_id === null) {
            $data = $request->validate([
                'label_en'   => 'nullable|string|max:120',
                'label_km'   => 'nullable|string|max:120',
                'value'      => 'nullable|string|max:255',
                'color'      => 'nullable|string|max:20',
                'icon'       => 'nullable|string|max:40',
                'sort_order' => 'nullable|integer|min:0',
                'is_active'  => 'nullable|boolean',
            ]);

            StoreSettingModel::updateOrCreate(
                ['clinic_id' => $this->clinicId, 'group' => $setting->group, 'key' => $setting->key],
                array_merge($data, ['is_system' => false])
            );

            return back()->with('flash', __('app.update') . ' ✓ (clinic override created)');
        }

        // Clinic-owned setting — direct update
        $setting->update($request->validate([
            'label_en'   => 'nullable|string|max:120',
            'label_km'   => 'nullable|string|max:120',
            'value'      => 'nullable|string|max:255',
            'color'      => 'nullable|string|max:20',
            'icon'       => 'nullable|string|max:40',
            'sort_order' => 'nullable|integer|min:0',
            'is_active'  => 'nullable|boolean',
        ]));

        return back()->with('flash', __('app.update') . ' ✓');
    }

    /**
     * Delete a clinic-owned setting (cannot delete system settings).
     */
    public function destroy(int $id): RedirectResponse
    {
        $setting = StoreSettingModel::withoutGlobalScope('clinic')->findOrFail($id);

        if ($setting->is_system && $setting->clinic_id === null) {
            return back()->with('flash_error', 'Cannot delete system settings.');
        }

        $setting->delete();

        return back()->with('flash', __('app.delete') . ' ✓');
    }
}
