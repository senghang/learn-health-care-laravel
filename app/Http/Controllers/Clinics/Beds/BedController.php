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
 * BedController — Ward → Room → Bed hierarchy.
 */
class BedController extends Controller
{
    // ── Ward list ─────────────────────────────────────────────────────────────

    public function index(): View
    {
        $wards = WardModel::where('clinic_id', currentClinic()->id)
            ->withCount([
                'beds',
                'beds as occupied_count'  => fn($q) => $q->where('status', 'occupied'),
                'beds as available_count' => fn($q) => $q->where('status', 'available'),
            ])
            ->with(['rooms' => fn($q) => $q->withCount('beds')->orderBy('floor')->orderBy('name')])
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
            'name'     => 'required|string|max:120',
            'name_kh'  => 'nullable|string|max:120',
            'name_en'  => 'nullable|string|max:120',
            'type'     => 'required|in:IPD,OPD,ICU,Emergency,Theatre,Outpatient',
            'capacity' => 'required|integer|min:0',
        ]);

        WardModel::create(array_merge($data, [
            'clinic_id' => currentClinic()->id,
            'code'      => \App\Services\ClinicCodeService::next(currentClinic()->id, 'WD'),
        ]));

        return redirect()->route('beds.index')->with('flash', 'Ward created successfully.');
    }

    public function wardEdit(int $id): View
    {
        $ward = WardModel::where('clinic_id', currentClinic()->id)
            ->with(['rooms' => fn($q) => $q->withCount('beds')->orderBy('floor')->orderBy('name')])
            ->findOrFail($id);
        return view('clinics.beds.ward-form', compact('ward'));
    }

    public function wardUpdate(Request $request, int $id): RedirectResponse
    {
        $ward = WardModel::where('clinic_id', currentClinic()->id)->findOrFail($id);

        $data = $request->validate([
            'name'      => 'required|string|max:120',
            'name_kh'   => 'nullable|string|max:120',
            'name_en'   => 'nullable|string|max:120',
            'type'      => 'required|in:IPD,OPD,ICU,Emergency,Theatre,Outpatient',
            'capacity'  => 'required|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $ward->update($data);

        return redirect()->route('beds.index')->with('flash', 'Ward updated.');
    }

    public function wardDelete(int $id): RedirectResponse
    {
        $ward = WardModel::where('clinic_id', currentClinic()->id)->findOrFail($id);
        $ward->delete();
        return redirect()->route('beds.index')->with('flash', 'Ward deleted.');
    }

    // ── Room CRUD ─────────────────────────────────────────────────────────────

    public function roomCreate(int $wardId): View
    {
        $ward = WardModel::where('clinic_id', currentClinic()->id)->findOrFail($wardId);
        return view('clinics.beds.room-form', ['ward' => $ward, 'room' => null]);
    }

    public function roomStore(Request $request, int $wardId): RedirectResponse
    {
        WardModel::where('clinic_id', currentClinic()->id)->findOrFail($wardId);

        $data = $request->validate([
            'name'  => 'required|string|max:120',
            'type'  => 'required|in:general,isolation,icu,theatre,observation',
            'floor' => 'required|integer|min:0|max:50',
        ]);

        RoomModel::create(array_merge($data, [
            'ward_id' => $wardId,
            'code'    => \App\Services\ClinicCodeService::next(currentClinic()->id, 'RM'),
        ]));

        return redirect()->route('beds.ward', $wardId)->with('flash', 'Room created.');
    }

    public function roomEdit(int $wardId, int $roomId): View
    {
        $ward = WardModel::where('clinic_id', currentClinic()->id)->findOrFail($wardId);
        $room = RoomModel::where('ward_id', $wardId)->findOrFail($roomId);
        return view('clinics.beds.room-form', compact('ward', 'room'));
    }

    public function roomUpdate(Request $request, int $wardId, int $roomId): RedirectResponse
    {
        WardModel::where('clinic_id', currentClinic()->id)->findOrFail($wardId);
        $room = RoomModel::where('ward_id', $wardId)->findOrFail($roomId);

        $data = $request->validate([
            'name'      => 'required|string|max:120',
            'type'      => 'required|in:general,isolation,icu,theatre,observation',
            'floor'     => 'required|integer|min:0|max:50',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $room->update($data);

        return redirect()->route('beds.ward', $wardId)->with('flash', 'Room updated.');
    }

    public function roomDelete(int $wardId, int $roomId): RedirectResponse
    {
        WardModel::where('clinic_id', currentClinic()->id)->findOrFail($wardId);
        $room = RoomModel::where('ward_id', $wardId)->findOrFail($roomId);
        $room->delete();
        return redirect()->route('beds.ward', $wardId)->with('flash', 'Room deleted.');
    }

    // ── Bed list for a ward ───────────────────────────────────────────────────

    public function beds(int $wardId): View
    {
        $ward = WardModel::where('clinic_id', currentClinic()->id)->findOrFail($wardId);

        $rooms = RoomModel::where('ward_id', $wardId)
            ->with(['beds' => fn($q) => $q->orderBy('name')])
            ->withCount('beds')
            ->orderBy('floor')
            ->orderBy('name')
            ->get();

        $stats = [
            'total'       => $ward->beds()->count(),
            'occupied'    => $ward->beds()->where('status', 'occupied')->count(),
            'cleaning'    => $ward->beds()->where('status', 'cleaning')->count(),
            'available'   => $ward->beds()->where('status', 'available')->count(),
            'reserved'    => $ward->beds()->where('status', 'reserved')->count(),
            'maintenance' => $ward->beds()->where('status', 'maintenance')->count(),
        ];

        return view('clinics.beds.beds', compact('ward', 'rooms', 'stats'));
    }

    // ── Bed CRUD ──────────────────────────────────────────────────────────────

    public function bedCreate(int $wardId): View
    {
        $ward  = WardModel::where('clinic_id', currentClinic()->id)->findOrFail($wardId);
        $rooms = RoomModel::where('ward_id', $wardId)->orderBy('name')->get();
        return view('clinics.beds.bed-form', ['ward' => $ward, 'rooms' => $rooms, 'bed' => null]);
    }

    public function bedStore(Request $request, int $wardId): RedirectResponse
    {
        $ward = WardModel::where('clinic_id', currentClinic()->id)->findOrFail($wardId);

        $data = $request->validate([
            'name'    => 'required|string|max:60',
            'room_id' => 'required|exists:rooms,id',
            'type'    => 'nullable|string|max:30',
        ]);

        BedModel::create(array_merge($data, [
            'ward_id' => $wardId,
            'status'  => 'available',
            'code'    => \App\Services\ClinicCodeService::next(currentClinic()->id, 'BD'),
        ]));

        // Keep capacity in sync
        $ward->update(['capacity' => $ward->beds()->count()]);

        return redirect()->route('beds.ward', $wardId)->with('flash', 'Bed added.');
    }

    public function bedEdit(int $wardId, int $bedId): View
    {
        $ward  = WardModel::where('clinic_id', currentClinic()->id)->findOrFail($wardId);
        $rooms = RoomModel::where('ward_id', $wardId)->orderBy('name')->get();
        $bed   = BedModel::where('ward_id', $wardId)->findOrFail($bedId);
        return view('clinics.beds.bed-form', compact('ward', 'rooms', 'bed'));
    }

    public function bedUpdate(Request $request, int $wardId, int $bedId): RedirectResponse
    {
        WardModel::where('clinic_id', currentClinic()->id)->findOrFail($wardId);
        $bed = BedModel::where('ward_id', $wardId)->findOrFail($bedId);

        $data = $request->validate([
            'name'      => 'required|string|max:60',
            'room_id'   => 'required|exists:rooms,id',
            'type'      => 'nullable|string|max:30',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $bed->update($data);

        return redirect()->route('beds.ward', $wardId)->with('flash', 'Bed updated.');
    }

    public function bedDelete(int $wardId, int $bedId): RedirectResponse
    {
        $ward = WardModel::where('clinic_id', currentClinic()->id)->findOrFail($wardId);
        $bed  = BedModel::where('ward_id', $wardId)->findOrFail($bedId);
        $bed->delete();
        $ward->update(['capacity' => $ward->beds()->count()]);
        return redirect()->route('beds.ward', $wardId)->with('flash', 'Bed deleted.');
    }

    // ── Status quick-update (PATCH /beds/beds/{id}/status) ───────────────────

    public function updateStatus(Request $request, int $bedId): JsonResponse
    {
        $bed = BedModel::whereHas('ward', fn($q) => $q->where('clinic_id', currentClinic()->id))
            ->findOrFail($bedId);

        $data = $request->validate([
            'status' => 'required|in:' . implode(',', BedModel::STATUSES),
        ]);

        if ($data['status'] === 'available') {
            $bed->release();
        } else {
            $bed->update(['status' => $data['status']]);
        }

        return response()->json([
            'id'     => $bed->id,
            'status' => $bed->status,
            'label'  => ucfirst($bed->status),
        ]);
    }

    // ── JSON: available beds ─────────────────────────────────────────────────

    public function available(Request $request): JsonResponse
    {
        $wardId = $request->get('ward_id');

        $query = BedModel::where('status', 'available')
            ->whereHas('ward', fn($q) => $q->where('clinic_id', currentClinic()->id))
            ->with('room', 'ward')
            ->orderBy('ward_id')
            ->orderBy('name');

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
}
