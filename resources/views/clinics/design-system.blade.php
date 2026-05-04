@extends('clinics.layout.app')
@section('title', 'Design System')

@section('content')

<x-ui.page-header km="ប្រព័ន្ធរចនា" title="Design System — Component Reference">
    <x-slot:actions>
        <x-ui.badge variant="success" dot>Live</x-ui.badge>
    </x-slot:actions>
</x-ui.page-header>

{{-- ══════════════════════════════════════════════════════════════════
     TOC
     ══════════════════════════════════════════════════════════════════ --}}
<div class="flex flex-wrap gap-2 mb-6 text-xs">
    @foreach(['Colors','Typography','Buttons','Badges','Alerts','Inputs','Selects','Cards','Stats','Modals','Empty States','Skeletons'] as $sec)
        <a href="#ds-{{ Str::slug($sec) }}"
           class="px-3 py-1.5 rounded-full border font-medium transition-colors hover:bg-[#EEF0FD] hover:text-[#4154f1] hover:border-[#C7CDFB]"
           style="border-color:#E2E8F0;color:#475569">
            {{ $sec }}
        </a>
    @endforeach
</div>

{{-- Section helper macro --}}
@php
function dsSection(string $id, string $km, string $en): void {
    echo '<h2 id="' . $id . '" class="text-base font-black mt-8 mb-3 pb-2 flex items-center gap-2"
              style="border-bottom:2px solid #EEF0FD;color:#0F172A">'
       . '<span style="font-family:var(--font-khmer,sans-serif)">' . $km . '</span>'
       . '<span style="font-size:12px;font-weight:400;color:#94A3B8">/ ' . $en . '</span>'
       . '</h2>';
}
@endphp

{{-- ══ 1. COLORS ════════════════════════════════════════════════════ --}}
{!! dsSection('ds-colors','ពណ៌','Colors') !!}

<x-ui.card class="mb-6">
<div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3">
    @foreach([
        ['Brand',    '#4154f1', '#EEF0FD'],
        ['Success',  '#10B981', '#D1FAE5'],
        ['Warning',  '#F59E0B', '#FEF3C7'],
        ['Danger',   '#EF4444', '#FEE2E2'],
        ['Info',     '#3B82F6', '#DBEAFE'],
        ['Muted',    '#94A3B8', '#F1F5F9'],
    ] as [$name, $color, $light])
        <div>
            <div class="h-14 rounded-lg mb-1.5" style="background:{{ $color }}"></div>
            <div class="h-7 rounded-lg mb-1.5" style="background:{{ $light }}"></div>
            <div class="text-xs font-semibold" style="color:#0F172A">{{ $name }}</div>
            <div class="text-[10px] font-mono" style="color:#94A3B8">{{ $color }}</div>
        </div>
    @endforeach
</div>
</x-ui.card>

{{-- ══ 2. TYPOGRAPHY ════════════════════════════════════════════════ --}}
{!! dsSection('ds-typography','អក្សរ','Typography') !!}

<x-ui.card class="mb-6">
    <div class="space-y-4 divide-y divide-[#F1F5F9]">
        {{-- Khmer scale --}}
        <div class="pb-4">
            <div class="text-[10px] font-bold uppercase tracking-widest mb-3" style="color:#94A3B8">Khmer — Noto Sans Khmer</div>
            @foreach([
                ['text-2xl font-black',  'ចុះឈ្មោះអ្នកជំងឺ', '24px / Black — Page titles'],
                ['text-xl  font-bold',   'ការព្យាបាល',        '20px / Bold — Section heads'],
                ['text-base font-semibold','ព័ត៌មានអ្នកជំងឺ','16px / Semibold — Card headers'],
                ['text-sm  font-normal',  'ឈ្មោះ: សុខ ចាន់ថា', '14px / Regular — Body text'],
                ['text-xs  font-normal',  'ថ្ងៃទី ០១ មករា ២០២៦', '12px / Regular — Captions'],
            ] as [$cls, $sample, $meta])
                <div class="flex items-baseline justify-between py-1.5 gap-4">
                    <span class="{{ $cls }}" style="font-family:var(--font-khmer,'Noto Sans Khmer',sans-serif);color:#0F172A">
                        {{ $sample }}
                    </span>
                    <span class="text-[10px] font-mono flex-shrink-0" style="color:#94A3B8">{{ $meta }}</span>
                </div>
            @endforeach
        </div>

        {{-- Latin scale --}}
        <div class="pt-4">
            <div class="text-[10px] font-bold uppercase tracking-widest mb-3" style="color:#94A3B8">Latin — Inter</div>
            @foreach([
                ['text-2xl font-black',  'Patient Registration', '24px / Black — Page titles'],
                ['text-xl  font-bold',   'Clinical Summary',     '20px / Bold — Section heads'],
                ['text-base font-semibold','Patient Details',    '16px / Semibold — Card headers'],
                ['text-sm  font-normal',  'Sok Chantha · PT-0042', '14px / Regular — Body'],
                ['text-xs  font-normal',  '01 January 2026 · 14:32', '12px / Regular — Meta'],
                ['text-xs  font-mono',    'PT-2026-0042',        '12px / Mono — Codes'],
            ] as [$cls, $sample, $meta])
                <div class="flex items-baseline justify-between py-1.5 gap-4">
                    <span class="{{ $cls }}" style="color:#0F172A">{{ $sample }}</span>
                    <span class="text-[10px] font-mono flex-shrink-0" style="color:#94A3B8">{{ $meta }}</span>
                </div>
            @endforeach
        </div>
    </div>
</x-ui.card>

{{-- ══ 3. BUTTONS ═══════════════════════════════════════════════════ --}}
{!! dsSection('ds-buttons','ប៊ូតុង','Buttons') !!}

<x-ui.card class="mb-6">

    {{-- Variants --}}
    <div class="mb-5">
        <div class="text-[10px] font-bold uppercase tracking-widest mb-3" style="color:#94A3B8">Variants</div>
        <div class="flex flex-wrap gap-2">
            <x-ui.button variant="primary">
                <x-slot:icon><i class="bi bi-check2-circle"></i></x-slot:icon>
                Primary
            </x-ui.button>
            <x-ui.button variant="secondary">
                <x-slot:icon><i class="bi bi-x-circle"></i></x-slot:icon>
                Secondary
            </x-ui.button>
            <x-ui.button variant="success">
                <x-slot:icon><i class="bi bi-person-plus-fill"></i></x-slot:icon>
                Success
            </x-ui.button>
            <x-ui.button variant="warning">
                <x-slot:icon><i class="bi bi-exclamation-triangle-fill"></i></x-slot:icon>
                Warning
            </x-ui.button>
            <x-ui.button variant="danger">
                <x-slot:icon><i class="bi bi-trash-fill"></i></x-slot:icon>
                Danger
            </x-ui.button>
            <x-ui.button variant="ghost">
                <x-slot:icon><i class="bi bi-arrow-left"></i></x-slot:icon>
                Ghost
            </x-ui.button>
        </div>
    </div>

    {{-- Sizes --}}
    <div class="mb-5">
        <div class="text-[10px] font-bold uppercase tracking-widest mb-3" style="color:#94A3B8">Sizes</div>
        <div class="flex flex-wrap items-center gap-2">
            <x-ui.button variant="primary" size="sm">Small (32px)</x-ui.button>
            <x-ui.button variant="primary" size="md">Medium (40px)</x-ui.button>
            <x-ui.button variant="primary" size="lg">Large (48px)</x-ui.button>
        </div>
    </div>

    {{-- States --}}
    <div class="mb-5">
        <div class="text-[10px] font-bold uppercase tracking-widest mb-3" style="color:#94A3B8">States</div>
        <div class="flex flex-wrap gap-2">
            <x-ui.button variant="primary">Normal</x-ui.button>
            <x-ui.button variant="primary" :loading="true">Saving…</x-ui.button>
            <x-ui.button variant="primary" :disabled="true">Disabled</x-ui.button>
            <x-ui.button variant="secondary" href="#ds-buttons">Link Button</x-ui.button>
        </div>
    </div>

    {{-- Full width --}}
    <div>
        <div class="text-[10px] font-bold uppercase tracking-widest mb-3" style="color:#94A3B8">Full Width</div>
        <div class="max-w-xs space-y-2">
            <x-ui.button variant="primary" :fullWidth="true">
                <x-slot:icon><i class="bi bi-check2-circle"></i></x-slot:icon>
                Save Patient
            </x-ui.button>
            <x-ui.button variant="secondary" :fullWidth="true">Cancel</x-ui.button>
        </div>
    </div>

</x-ui.card>

{{-- ══ 4. BADGES ════════════════════════════════════════════════════ --}}
{!! dsSection('ds-badges','ស្លាក','Badges') !!}

<x-ui.card class="mb-6">
    <div class="flex flex-wrap gap-2 mb-4">
        <x-ui.badge variant="primary">Primary</x-ui.badge>
        <x-ui.badge variant="secondary">Secondary</x-ui.badge>
        <x-ui.badge variant="success">Active</x-ui.badge>
        <x-ui.badge variant="warning">Pending</x-ui.badge>
        <x-ui.badge variant="danger">Critical</x-ui.badge>
        <x-ui.badge variant="info">Info</x-ui.badge>
        <x-ui.badge variant="admitted">Admitted</x-ui.badge>
        <x-ui.badge variant="discharged">Discharged</x-ui.badge>
        <x-ui.badge variant="paid">Paid</x-ui.badge>
        <x-ui.badge variant="unpaid">Unpaid</x-ui.badge>
    </div>
    <div class="flex flex-wrap gap-2 mb-4">
        <x-ui.badge variant="primary" dot>Live OPD</x-ui.badge>
        <x-ui.badge variant="success" dot>Online</x-ui.badge>
        <x-ui.badge variant="danger"  dot>Emergency</x-ui.badge>
        <x-ui.badge variant="warning" dot>Review</x-ui.badge>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <x-ui.badge variant="success" size="sm">sm</x-ui.badge>
        <x-ui.badge variant="success" size="md">md</x-ui.badge>
        <x-ui.badge variant="success" size="lg">lg</x-ui.badge>
    </div>
</x-ui.card>

{{-- ══ 5. ALERTS ════════════════════════════════════════════════════ --}}
{!! dsSection('ds-alerts','សារជូនដំណឹង','Alerts') !!}

<div class="space-y-3 mb-6">
    <x-ui.alert type="success" title="Patient saved">
        Patient PT-2026-0042 was registered successfully and assigned to Dr. Chan.
    </x-ui.alert>
    <x-ui.alert type="error" title="Validation error">
        Surname and given name are required. Please complete all required fields.
    </x-ui.alert>
    <x-ui.alert type="warning" title="Low stock alert" :dismissible="true">
        Amoxicillin 500mg has only 12 units remaining. Consider reordering.
    </x-ui.alert>
    <x-ui.alert type="info" title="Lab results ready">
        CBC results for PT-0042 are available for review.
    </x-ui.alert>
</div>

{{-- ══ 6. INPUTS ════════════════════════════════════════════════════ --}}
{!! dsSection('ds-inputs','ប្រអប់បញ្ចូល','Form Inputs') !!}

<x-ui.card class="mb-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

        {{-- Default --}}
        <div class="space-y-1.5">
            <label class="block text-xs font-medium" style="color:#475569">Default</label>
            <x-forms.input name="_demo_default" placeholder="Enter value…" />
        </div>

        {{-- With value --}}
        <div class="space-y-1.5">
            <label class="block text-xs font-medium" style="color:#475569">Filled</label>
            <x-forms.input name="_demo_filled" value="Sok Chantha" />
        </div>

        {{-- Error state --}}
        <div class="space-y-1.5">
            <label class="block text-xs font-medium" style="color:#475569">
                Error state <span style="color:#EF4444">*</span>
            </label>
            <x-forms.input name="_demo_error" :hasError="true" value="bad value" />
            <p class="text-xs" style="color:#EF4444">
                <i class="bi bi-exclamation-circle" aria-hidden="true"></i> This field is invalid.
            </p>
        </div>

        {{-- Disabled --}}
        <div class="space-y-1.5">
            <label class="block text-xs font-medium" style="color:#94A3B8">Disabled</label>
            <x-forms.input name="_demo_disabled" value="Not editable" disabled />
        </div>

        {{-- Readonly --}}
        <div class="space-y-1.5">
            <label class="block text-xs font-medium" style="color:#475569">
                Readonly (Auto-generated)
                <span class="ml-1 text-[10px] font-bold px-1.5 py-0.5 rounded-full"
                      style="background:#D1FAE5;color:#065F46">AUTO</span>
            </label>
            <x-forms.input name="_demo_readonly" value="PT-2026-0042" readonly
                           style="font-family:var(--font-mono,monospace);font-weight:700;color:#4154f1" />
        </div>

        {{-- Date --}}
        <div class="space-y-1.5">
            <label class="block text-xs font-medium" style="color:#475569">Date</label>
            <x-forms.input type="date" name="_demo_date" value="{{ date('Y-m-d') }}" />
        </div>

        {{-- Tel --}}
        <div class="space-y-1.5">
            <label class="block text-xs font-medium" style="color:#475569">Phone</label>
            <x-forms.input type="tel" name="_demo_tel" placeholder="012 345 678" />
        </div>

        {{-- Search --}}
        <div class="space-y-1.5">
            <label class="block text-xs font-medium" style="color:#475569">Search</label>
            <div class="relative">
                <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#94A3B8"></i>
                <x-forms.input type="search" name="_demo_search" placeholder="Search patients…" class="pl-9" />
            </div>
        </div>

    </div>
</x-ui.card>

{{-- ══ 7. SELECTS ════════════════════════════════════════════════════ --}}
{!! dsSection('ds-selects','ការជ្រើស','Select Dropdowns') !!}

<x-ui.card class="mb-6">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="space-y-1.5">
            <label class="block text-xs font-medium" style="color:#475569">Default</label>
            <x-forms.select name="_demo_sel">
                <option value="">— Choose —</option>
                <option value="M">Male</option>
                <option value="F">Female</option>
            </x-forms.select>
        </div>
        <div class="space-y-1.5">
            <label class="block text-xs font-medium" style="color:#475569">With value</label>
            <x-forms.select name="_demo_sel2">
                <option value="">—</option>
                <option value="M" selected>♂ ប្រុស / Male</option>
                <option value="F">♀ ស្រី / Female</option>
            </x-forms.select>
        </div>
        <div class="space-y-1.5">
            <label class="block text-xs font-medium" style="color:#94A3B8">Disabled</label>
            <x-forms.select name="_demo_sel3" disabled>
                <option value="A+">A+</option>
            </x-forms.select>
        </div>
    </div>
</x-ui.card>

{{-- ══ 8. CARDS ══════════════════════════════════════════════════════ --}}
{!! dsSection('ds-cards','កាត','Cards') !!}

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">

    {{-- Simple card --}}
    <x-ui.card>
        <x-slot:header>
            <x-ui.card-header km="ព័ត៌មាន" label="Information" icon="bi-info-circle-fill" />
        </x-slot:header>
        <p class="text-sm" style="color:#475569">Card with bilingual header. The Khmer label is dominant (14px bold),
        English is the secondary qualifier (12px muted).</p>
    </x-ui.card>

    {{-- Card with actions --}}
    <x-ui.card>
        <x-slot:header>
            <x-ui.card-header km="វេជ្ជបញ្ជា" label="Prescriptions" icon="bi-capsule-fill"
                              iconColor="#9B59B6" :count="12">
                <x-slot:actions>
                    <x-ui.button size="sm" variant="secondary">
                        <x-slot:icon><i class="bi bi-plus-lg"></i></x-slot:icon>
                        Add
                    </x-ui.button>
                </x-slot:actions>
            </x-ui.card-header>
        </x-slot:header>
        <p class="text-sm" style="color:#475569">Card with count badge and action button in the header.</p>
    </x-ui.card>

    {{-- Table card (noPadding) --}}
    <x-ui.card noPadding class="sm:col-span-2">
        <x-slot:header>
            <x-ui.card-header km="អ្នកជំងឺ" label="Patient List" icon="bi-people-fill" :count="3" />
        </x-slot:header>
        <table class="w-full text-xs">
            <thead>
                <tr style="background:#F8FAFC;border-bottom:1px solid #E2E8F0">
                    <th class="text-left px-5 py-2.5 font-semibold" style="color:#475569">Code</th>
                    <th class="text-left px-5 py-2.5 font-semibold" style="color:#475569">Name</th>
                    <th class="text-left px-5 py-2.5 font-semibold hidden sm:table-cell" style="color:#475569">Age</th>
                    <th class="text-left px-5 py-2.5 font-semibold" style="color:#475569">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach([
                    ['PT-0001','សុខ ចាន់ថា','32', 'Active'],
                    ['PT-0002','ហ៊ុន ស្រីមុំ','28', 'Active'],
                    ['PT-0003','ឈីន ដារា',  '61', 'Inactive'],
                ] as [$code, $name, $age, $status])
                    <tr class="border-b hover:bg-[#F8FAFC] transition-colors" style="border-color:#F1F5F9">
                        <td class="px-5 py-3 font-mono font-bold" style="color:#4154f1">{{ $code }}</td>
                        <td class="px-5 py-3 font-semibold" style="color:#0F172A;font-family:var(--font-khmer,sans-serif)">{{ $name }}</td>
                        <td class="px-5 py-3 hidden sm:table-cell" style="color:#475569">{{ $age }}y</td>
                        <td class="px-5 py-3">
                            <x-ui.badge :variant="$status === 'Active' ? 'success' : 'secondary'" size="sm">{{ $status }}</x-ui.badge>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-ui.card>

</div>

{{-- ══ 9. STATS CARDS ═══════════════════════════════════════════════ --}}
{!! dsSection('ds-stats','ស្ថិតិ','Stats Cards') !!}

<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <x-ui.stats-card value="248"  label="Total Patients" km="អ្នកជំងឺសរុប"   icon="bi-people-fill"       color="#4154f1" bg="#EEF0FD" />
    <x-ui.stats-card value="14"   label="Today Visits"   km="ចូលទស្សន ថ្ងៃនេះ" icon="bi-calendar-check-fill" color="#10B981" bg="#D1FAE5" />
    <x-ui.stats-card value="3"    label="IPD Admitted"   km="ចូលព្យាបាល"      icon="bi-bed-fill"           color="#F59E0B" bg="#FEF3C7" trend="up" trendValue="+2" />
    <x-ui.stats-card value="$842" label="Today Revenue"  km="ចំណូលថ្ងៃនេះ"   icon="bi-currency-dollar"   color="#EF4444" bg="#FEE2E2" :live="true" />
</div>

{{-- ══ 10. MODALS ═══════════════════════════════════════════════════ --}}
{!! dsSection('ds-modals','បង្អួច','Modals') !!}

<x-ui.card class="mb-6">
    <div class="flex flex-wrap gap-2">

        <x-ui.modal id="demoModalSm" title="Confirm Delete" size="sm">
            <x-slot:trigger>
                <x-ui.button variant="secondary" size="sm">Small Modal</x-ui.button>
            </x-slot:trigger>
            <p class="text-sm" style="color:#475569">Are you sure you want to delete this record? This action cannot be undone.</p>
            <x-slot:footer>
                <x-ui.button variant="secondary" size="sm" @click="open = false">Cancel</x-ui.button>
                <x-ui.button variant="danger"    size="sm">Delete</x-ui.button>
            </x-slot:footer>
        </x-ui.modal>

        <x-ui.modal id="demoModalMd" km="ចុះឈ្មោះ" title="Register Patient" size="md">
            <x-slot:trigger>
                <x-ui.button variant="primary" size="sm">Medium Modal</x-ui.button>
            </x-slot:trigger>
            <div class="space-y-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium" style="color:#475569">Patient Code</label>
                    <x-forms.input name="_m_code" value="PT-2026-0042" readonly />
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium" style="color:#475569">Name <span style="color:#EF4444">*</span></label>
                    <x-forms.input name="_m_name" placeholder="Full name" />
                </div>
            </div>
            <x-slot:footer>
                <x-ui.button variant="secondary" @click="open = false">Cancel</x-ui.button>
                <x-ui.button variant="primary">Save</x-ui.button>
            </x-slot:footer>
        </x-ui.modal>

        <x-ui.modal id="demoModalLg" km="IPD ការចូលព្យាបាល" title="Admit Patient" size="lg">
            <x-slot:trigger>
                <x-ui.button variant="warning" size="sm">Large Modal</x-ui.button>
            </x-slot:trigger>
            <p class="text-sm mb-4" style="color:#475569">Large modal for complex forms like patient admission.</p>
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium" style="color:#475569">Ward</label>
                    <x-forms.select name="_m_ward">
                        <option value="">— Select Ward —</option>
                        <option value="general">General Ward</option>
                        <option value="icu">ICU</option>
                    </x-forms.select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium" style="color:#475569">Bed</label>
                    <x-forms.select name="_m_bed">
                        <option value="">— Select Bed —</option>
                        <option value="A1">A1</option>
                        <option value="A2">A2</option>
                    </x-forms.select>
                </div>
            </div>
            <x-slot:footer>
                <x-ui.button variant="secondary" @click="open = false">Cancel</x-ui.button>
                <x-ui.button variant="primary">Admit Patient</x-ui.button>
            </x-slot:footer>
        </x-ui.modal>

    </div>
</x-ui.card>

{{-- ══ 11. EMPTY STATES ══════════════════════════════════════════════ --}}
{!! dsSection('ds-empty-states','ទទេ','Empty States') !!}

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
    <x-ui.card>
        <x-ui.empty-state icon="bi-person-circle"
            title="No patients found"
            description="Register the first patient to get started." compact>
            <x-ui.button variant="primary" size="sm">
                <x-slot:icon><i class="bi bi-person-plus-fill"></i></x-slot:icon>
                Register Patient
            </x-ui.button>
        </x-ui.empty-state>
    </x-ui.card>
    <x-ui.card>
        <x-ui.empty-state icon="bi-receipt"
            title="No invoices yet"
            description="Invoices will appear here once a visit is billed." compact>
        </x-ui.empty-state>
    </x-ui.card>
</div>

{{-- ══ 12. SKELETON LOADERS ═════════════════════════════════════════ --}}
{!! dsSection('ds-skeletons','Loading…','Skeleton Loaders') !!}

<x-ui.card class="mb-6">
    <div class="space-y-4">

        {{-- Simulated patient card skeleton --}}
        <div class="flex items-center gap-3">
            <div class="skeleton w-10 h-10 rounded-xl flex-shrink-0"></div>
            <div class="flex-1 space-y-2">
                <div class="skeleton h-4 rounded" style="width:40%"></div>
                <div class="skeleton h-3 rounded" style="width:60%"></div>
            </div>
            <div class="skeleton h-6 w-16 rounded-full"></div>
        </div>

        {{-- Simulated stat card skeleton --}}
        <div class="grid grid-cols-4 gap-3">
            @for($i = 0; $i < 4; $i++)
                <div class="p-4 rounded-xl border border-[#E2E8F0] space-y-3">
                    <div class="skeleton h-9 w-9 rounded-xl"></div>
                    <div class="skeleton h-7 w-3/4 rounded"></div>
                    <div class="skeleton h-3 w-full rounded"></div>
                    <div class="skeleton h-3 w-2/3 rounded"></div>
                </div>
            @endfor
        </div>

        {{-- Form field skeletons --}}
        <div class="grid grid-cols-3 gap-3">
            @for($i = 0; $i < 6; $i++)
                <div class="space-y-1.5">
                    <div class="skeleton h-3 w-2/3 rounded"></div>
                    <div class="skeleton h-10 w-full rounded-md"></div>
                </div>
            @endfor
        </div>

    </div>
</x-ui.card>

{{-- ══ TOKEN REFERENCE ══════════════════════════════════════════════ --}}
<h2 class="text-base font-black mt-8 mb-3 pb-2" style="border-bottom:2px solid #EEF0FD;color:#0F172A">
    CSS Custom Properties Reference
</h2>

<x-ui.card noPadding class="mb-8">
    <table class="w-full text-xs font-mono">
        <thead>
            <tr style="background:#F8FAFC;border-bottom:1px solid #E2E8F0">
                <th class="text-left px-5 py-2.5 font-semibold" style="color:#475569">Token</th>
                <th class="text-left px-5 py-2.5 font-semibold" style="color:#475569">Value</th>
                <th class="text-left px-5 py-2.5 font-semibold hidden md:table-cell" style="color:#475569">Usage</th>
            </tr>
        </thead>
        <tbody>
            @foreach([
                ['--brand',          '#4154F1',  'Primary actions, links, focus rings'],
                ['--brand-light',    '#EEF0FD',  'Hover backgrounds, tinted badges'],
                ['--success',        '#10B981',  'Active, paid, online states'],
                ['--warning',        '#F59E0B',  'Pending, review, IPD'],
                ['--danger',         '#EF4444',  'Errors, critical, destructive'],
                ['--info',           '#3B82F6',  'Informational, lab results'],
                ['--text-primary',   '#0F172A',  'Headings, strong labels'],
                ['--text-secondary', '#475569',  'Body, field labels'],
                ['--text-muted',     '#94A3B8',  'Placeholders, helper text, Khmer subtitles'],
                ['--border-subtle',  '#E2E8F0',  'Dividers, card borders'],
                ['--border-default', '#CBD5E1',  'Input borders'],
                ['--border-focus',   'var(--brand)','Focused inputs'],
                ['--radius-sm',      '6px',      'Buttons, inputs, chips'],
                ['--radius-md',      '8px',      'Cards, dropdowns'],
                ['--radius-lg',      '12px',     'Modals, large panels'],
                ['--input-height-md','40px',     'Default form control height'],
                ['--topbar-height',  '56px',     'Fixed topbar'],
                ['--sidebar-width',  '240px',    'Expanded sidebar'],
            ] as [$token, $value, $usage])
                <tr class="border-b hover:bg-[#F8FAFC]" style="border-color:#F1F5F9">
                    <td class="px-5 py-2.5" style="color:#4154f1">{{ $token }}</td>
                    <td class="px-5 py-2.5" style="color:#475569">{{ $value }}</td>
                    <td class="px-5 py-2.5 hidden md:table-cell" style="color:#94A3B8;font-family:sans-serif">{{ $usage }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</x-ui.card>

@endsection
