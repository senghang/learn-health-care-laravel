<?php

namespace App\Http\Controllers\Clinics\Beds;

use App\Http\Controllers\Controller;
use App\Models\BedModel;
use App\Models\RoomModel;
use App\Models\WardModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * BedController
 *
 * Manages the three-level bed hierarchy: wards → rooms → beds.
 * Also provides a JSON availability endpoint consumed by the workflow
 * registration step when assigning a bed to an IPD patient.
 */
class BedController extends Controller
{
    // ── Ward list ─────────────────────────────────────────────────────────────

    public function index(): View
    {
        $wards = WardModel::where('clinic_id', currentClinic()->id)
            ->withCount(['beds', 'beds as occupied_count' => fn($q) => $q->where('status', 'occupied')])
            ->with('rooms')
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return view('clinics.beds.index', compact('wards'));
    }

    // ── Ward CRUD ─────────────────────────────────────────────────────────────

    public function wardCreate(): View
    {
        return view('clinics.beds.ward-form', ['ward' => null]);
    }

    public function wardStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code'     => 'required|string|max:20|unique:wards,code',
            'name'     => 'required|string|max:120',
            'name_kh'  => 'nullable|string|max:120',
            'name_en'  => 'nullable|string|max:120',
            'type'     => 'required|in:IPD,OPD,ICU,Emergency,Theatre,Outpatient',
            'capacity' => 'required|integer|min:0',
        ]);

        WardModel::create(array_merge($data, ['clinic_id' => currentClinic()->id]));

        return redirect()->route('beds.index')->with('flash', 'Ward created.');
    }

    public function wardEdit(int $id): View
    {
        $ward = WardModel::where('clinic_id', currentClinic()->id)->findOrFail($id);
        return view('clinics.beds.ward-form', compact('ward'));
    }

    public function wardUpdate(Request $request, int $id): RedirectResponse
    {
        $ward = WardModel::where('clinic_id', currentClinic()->id)->findOrFail($id);

        $data = $request->validate([
            'name'     => 'required|string|max:120',
            'name_kh'  => 'nullable|string|max:120',
            'name_en'  => 'nullable|string|max:120',
            'type'     => 'required|in:IPD,OPD,ICU,Emergency,Theatre,Outpatient',
            'capacity' => 'required|integer|min:0',
            'is_active'=> 'boolean',
        ]);

        $ward->update($data);

        return redirect()->route('beds.index')->with('flash', 'Ward updated.');
    }

    // ── Bed list for a ward ───────────────────────────────────────────────────

    public function beds(int $wardId): View
    {
        $ward = WardModel::where('clinic_id', currentClinic()->id)->findOrFail($wardId);

        $rooms = RoomModel::where('ward_id', $wardId)
            ->with(['beds' => fn($q) => $q->orderBy('code')])
            ->orderBy('floor')
            ->orderBy('name')
            ->get();

        $stats = [
            'total'    => $ward->beds()->count(),
            'occupied' => $ward->beds()->where('status', 'occupied')->count(),
            'cleaning' => $ward->beds()->where('status', 'cleaning')->count(),
            'available'=> $ward->beds()->where('status', 'available')->count(),
        ];

        return view('clinics.beds.beds', compact('ward', 'rooms', 'stats'));
    }

    // ── Bed status quick-update ───────────────────────────────────────────────

    public function updateStatus(Request $request, int $bedId): JsonResponse
    {
        $bed = BedModel::whereHas('ward', fn($q) => $q->where('clinic_id', currentClinic()->id))
            ->findOrFail($bedId);

        $data = $request->validate([
            'status' => 'required|in:' . implode(',', BedModel::STATUSES),
        ]);

        if ($data['status'] === 'available') {
            $bed->release(); // clears patient/visit pointers
        } else {
            $bed->update(['status' => $data['status']]);
        }

        return response()->json([
            'id'     => $bed->id,
            'status' => $bed->status,
            'label'  => ucfirst($bed->status),
        ]);
    }

    // ── JSON: available beds for ward (used by registration step) ─────────────

    public function available(Request $request): JsonResponse
    {
        $wardId = $request->get('ward_id');

        $query = BedModel::where('status', 'available')
            ->whereHas('ward', fn($q) => $q->where('clinic_id', currentClinic()->id))
            ->with('room', 'ward')
            ->orderBy('ward_id')
            ->orderBy('code');

        if ($wardId) {
            $query->where('ward_id', $wardId);
        }

        $beds = $query->get()->map(fn(BedModel $b) => [
            'id'        => $b->id,
            'code'      => $b->code,
            'name'      => $b->name,
            'ward_name' => $b->ward?->name,
            'room_name' => $b->room?->name,
            'type'      => $b->type,
        ]);

        return response()->json($beds);
    }

    // ── Bed store ─────────────────────────────────────────────────────────────

    public function bedStore(Request $request, int $wardId): RedirectResponse
    {
        $ward = WardModel::where('clinic_id', currentClinic()->id)->findOrFail($wardId);

        $data = $request->validate([
            'code'    => 'required|string|max:20|unique:beds,code',
            'name'    => 'required|string|max:60',
            'room_id' => 'required|exists:rooms,id',
            'type'    => 'nullable|string|max:30',
        ]);

        BedModel::create(array_merge($data, ['ward_id' => $wardId, 'status' => 'available']));

        // Update ward capacity count
        $ward->update(['capacity' => $ward->beds()->count()]);

        return redirect()->route('beds.ward', $wardId)->with('flash', 'Bed added.');
    }
}
