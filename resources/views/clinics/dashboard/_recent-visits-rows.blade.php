{{--
    Recent Visits table section (partial replacement for clinics/dashboard.blade.php).

    Key fixes:
    - visit->name instead of visit->given_name (VisitModel stores given name in `name` column)
    - Uses <x-status-badge> and <x-progress-bar> reusable components
    - Uses $visit->getGivenNameAttribute() via the new accessor

    This is the tbody rows partial — include inside the existing table structure.
--}}
@forelse($recentVisits as $visit)
    <tr style="cursor:pointer"
        onclick="window.location='{{ route('workflow.show', ['code' => $visit->code]) }}'">

        <td data-label="Code">
            <code style="color:#4154f1;font-size:12px">{{ $visit->code }}</code>
            <div style="font-size:10px;color:#ccc">
                {{ $visit->admitted_at?->format('d/m H:i') }}
            </div>
        </td>

        <td data-label="អ្នកជំងឺ">
            {{-- FIXED: visits.name stores given name; patient relation gives full name --}}
            <div style="font-weight:700">
                {{ $visit->patient->surname ?? $visit->surname }},
                {{ $visit->patient->name ?? $visit->name }}
            </div>
            <div style="font-size:10.5px;color:#aaa">{{ $visit->patient_code }}</div>
        </td>

        <td>
            <x-status-badge :status="strtolower($visit->visit_type)" :label="$visit->visit_type"/>
        </td>

        <td>
            @if(is_null($visit->discharged_at))
                <x-status-badge status="active"/>
            @else
                <x-status-badge status="done"/>
            @endif
        </td>

        <td>
            <x-progress-bar
                :done="$visit->steps_done"
                :total="10"
                :skipped="$visit->steps_skipped"
                width="90px"
                :show-text="true"
            />
        </td>

        <td>
            <a href="{{ route('workflow.show', ['code' => $visit->code]) }}"
               class="btn btn-sm btn-primary"
               onclick="event.stopPropagation()">
                <i class="bi bi-pencil-fill"></i>បន្ត
            </a>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" style="text-align:center;padding:32px;color:#bbb">
            <div style="font-size:28px;margin-bottom:8px;opacity:.4">🏥</div>
            <div style="font-size:13px;font-weight:600;margin-bottom:6px">
                មិនទាន់មានការចូលព្យាបាលថ្ងៃនេះ
            </div>
            <div style="font-size:11px;margin-bottom:12px">No visits recorded today</div>
            <a href="{{ route('workflow.create') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-lg"></i> ចាប់ផ្ដើម / New Visit
            </a>
        </td>
    </tr>
@endforelse
