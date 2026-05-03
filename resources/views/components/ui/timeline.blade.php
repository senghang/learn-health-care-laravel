{{--
    Medical timeline — vertical event list for patient history, audit logs, etc.

    <x-ui.timeline>
        <x-ui.timeline-item
            date="2026-05-01 09:30"
            title="OPD Visit"
            km="ចូលពេទ្យក្រៅ"
            icon="bi-clipboard2-pulse-fill"
            color="#4154f1"
            by="Dr. Chan Dara">
            Chief complaint: fever and headache for 3 days.
        </x-ui.timeline-item>
        <x-ui.timeline-item
            date="2026-04-28"
            title="Lab Result"
            icon="bi-eyedropper"
            color="#2eca6a"
            status="completed">
            CBC result: WBC 9.2, Hgb 13.4 — within normal range.
        </x-ui.timeline-item>
    </x-ui.timeline>

    Props (timeline wrapper):
        compact — reduce vertical spacing

    Props (timeline-item — separate component):
        date    — ISO date string or formatted string
        title   — English event title
        km      — Khmer label (optional)
        icon    — Bootstrap icon class (default: bi-circle-fill)
        color   — dot/icon accent color (default: #4154f1)
        by      — actor name (doctor, nurse, system)
        status  — completed | pending | cancelled | null
--}}
@props(['compact' => false])

<ol class="relative {{ $compact ? 'space-y-3' : 'space-y-5' }}" role="list"
    style="padding-left:1.5rem;border-left:2px solid #e8ecff" {{ $attributes }}>
    {{ $slot }}
</ol>
