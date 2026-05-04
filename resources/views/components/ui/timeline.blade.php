{{--
    Medical timeline — vertical event list.

    <x-ui.timeline>
        <x-ui.timeline-item date="2026-05-01" title="OPD Visit" km="ចូលពេទ្យក្រៅ"
            icon="bi-clipboard2-pulse-fill" color="#4154f1" by="Dr. Chan">
            Chief complaint: fever for 3 days.
        </x-ui.timeline-item>
        <x-ui.timeline-item date="2026-04-28" title="Lab" icon="bi-eyedropper"
            color="#10B981" status="completed">
            CBC within normal range.
        </x-ui.timeline-item>
    </x-ui.timeline>

    Props:
        compact — bool reduce vertical spacing
--}}
@props(['compact' => false])

<ol class="relative {{ $compact ? 'space-y-3' : 'space-y-4' }}" role="list"
    style="padding-left:1.5rem;border-left:2px solid var(--brand-light,#EEF0FD)"
    {{ $attributes }}>
    {{ $slot }}
</ol>
