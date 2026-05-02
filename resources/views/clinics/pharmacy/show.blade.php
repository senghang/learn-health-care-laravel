@php use Illuminate\Support\MessageBag; @endphp
@extends('clinics.layout.app')
@section('title', 'Dispense ' . $prescription->code)

@php
    $items       = $stockCheck['items'];
    $errors      = $stockCheck['errors'];
    $warnings    = $stockCheck['warnings'];
    $canFull     = $stockCheck['can_full_dispense'];
    $anyPending  = collect($items)->where('already_dispensed', false)->where('can_dispense', true)->count();
    $isFullyDone = $prescription->dispensed_status === 'dispensed';
@endphp

@section('content')

    <x-page-header
        title="Dispense {{ $prescription->code }}"
        subtitle="ចែកចាយថ្នាំ"
        :breadcrumbs="[
        ['label' => __('app.home'),     'url' => route('dashboard')],
        ['label' => 'Pharmacy',         'url' => route('pharmacy.index')],
        ['label' => $prescription->code],
    ]">
        <a href="{{ route('print.prescription', $prescription->code) }}" class="btn btn-outline-secondary btn-sm"
           target="_blank">
            <i class="bi bi-printer-fill"></i> Print Rx
        </a>
        @if($prescription->visit_code)
            <a href="{{ url('/workflow/' . $prescription->visit_code) }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-diagram-3-fill"></i> Visit
            </a>
        @endif
    </x-page-header>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="note note-success mb-3">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('info'))
        <div class="note note-warn mb-3">
            <i class="bi bi-info-circle-fill"></i> {{ session('info') }}
        </div>
    @endif
    @if(isset($errors) && $errors instanceof MessageBag && $errors->has('dispense'))
        <div class="note note-danger mb-3">
            <i class="bi bi-exclamation-triangle-fill"></i> {{ $errors->first('dispense') }}
        </div>
    @endif

    <div class="row g-3">

        {{-- ── LEFT: Medication Items ───────────────────────────────────────────── --}}
        <div class="col-12 col-lg-8">

            {{-- Stock check alerts --}}
            @if(count($errors) > 0)
                <div class="note note-danger mb-3">
                    <div style="font-weight:800;margin-bottom:6px">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        Stock Errors — Cannot fully dispense:
                    </div>
                    @foreach($errors as $err)
                        <div style="font-size:12px;margin-top:4px">• {{ $err }}</div>
                    @endforeach
                </div>
            @endif
            @if(count($warnings) > 0)
                <div class="note note-warn mb-3">
                    <div style="font-weight:800;margin-bottom:4px">
                        <i class="bi bi-exclamation-circle-fill"></i> Warnings:
                    </div>
                    @foreach($warnings as $warn)
                        <div style="font-size:12px;margin-top:4px">• {{ $warn }}</div>
                    @endforeach
                </div>
            @endif

            {{-- Medication items table --}}
            <div class="card-emr mb-3">
                <div class="card-hd" style="background:#fdf0f8">
                    <div class="card-hd-title">
                        <i class="bi bi-capsule-fill" style="color:#e91e8c"></i>
                        ថ្នាំ / Medication Items
                        <span
                            style="background:#e91e8c22;color:#e91e8c;border:1px solid #e91e8c66;font-size:10px;padding:1px 8px;border-radius:10px;margin-left:6px">
                        {{ count($items) }} items
                    </span>
                    </div>
                    @if(!$isFullyDone)
                        <label
                            style="display:flex;align-items:center;gap:6px;font-size:11.5px;color:#4154f1;cursor:pointer">
                            <input type="checkbox" id="selectAll" onchange="toggleAll(this)"/>
                            Select all
                        </label>
                    @endif
                </div>
                <div class="card-bd" style="padding:0">
                    <div class="table-responsive">
                        <table class="tbl">
                            <thead>
                            <tr>
                                @if(!$isFullyDone)
                                    <th style="width:36px"></th>
                                @endif
                                <th>#</th>
                                <th>Medicine</th>
                                <th style="text-align:center">Dose (M/A/E/N)</th>
                                <th style="text-align:center">Days</th>
                                <th style="text-align:center">Need</th>
                                <th style="text-align:center">Stock</th>
                                <th style="text-align:center">Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($items as $i => $row)
                                @php
                                    $med       = $row['medication'];
                                    $medicine  = $row['medicine'];
                                    $needed    = $row['needed'];
                                    $available = $row['available'];
                                    $canDispense = $row['can_dispense'];
                                    $done      = $row['already_dispensed'];

                                    // Stock badge
                                    if ($available === null) {
                                        $stockCls = ''; $stockTxt = '— no code';
                                    } elseif ($available <= 0) {
                                        $stockCls = 'stock-out'; $stockTxt = 'Out of stock';
                                    } elseif (!$row['sufficient']) {
                                        $stockCls = 'stock-out'; $stockTxt = "Only {$available}";
                                    } elseif ($medicine && $available <= ($medicine->stock_alert ?? 10)) {
                                        $stockCls = 'stock-low'; $stockTxt = "Low: {$available}";
                                    } else {
                                        $stockCls = 'stock-ok'; $stockTxt = "✓ {$available}";
                                    }
                                @endphp
                                <tr style="{{ $done ? 'opacity:.55' : '' }}">
                                    @if(!$isFullyDone)
                                        <td style="text-align:center">
                                            @if($canDispense)
                                                <input type="checkbox" class="item-check"
                                                       name="item_ids[]" value="{{ $med->id }}"
                                                       form="partialForm" checked/>
                                            @elseif($done)
                                                <i class="bi bi-check-circle-fill"
                                                   style="color:#2eca6a;font-size:14px"></i>
                                            @else
                                                <i class="bi bi-x-circle-fill" style="color:#e74c3c;font-size:14px"></i>
                                            @endif
                                        </td>
                                    @endif
                                    <td style="color:#aaa;font-size:11px">{{ $i + 1 }}</td>
                                    <td>
                                        <div style="font-weight:700;color:#012970;font-size:13px">
                                            {{ $med->medicine_name }}
                                        </div>
                                        @if($med->strength)
                                            <code
                                                style="font-size:10.5px;background:#eef0fd;color:#4154f1;padding:1px 5px;border-radius:4px">
                                                {{ $med->strength }}
                                            </code>
                                        @endif
                                        @if($med->form)
                                            <span
                                                style="font-size:10px;color:#9b59b6;margin-left:4px">{{ $med->form }}</span>
                                        @endif
                                        @if($med->note)
                                            <div
                                                style="font-size:10px;color:#aaa;font-style:italic">{{ $med->note }}</div>
                                        @endif
                                        @if(!$med->medication_code)
                                            <div style="font-size:10px;color:#e74c3c;margin-top:2px">
                                                <i class="bi bi-exclamation-triangle-fill"></i> No catalogue code
                                            </div>
                                        @endif
                                    </td>
                                    <td style="text-align:center">
                                        @php
                                            $parts = array_filter([
                                                $med->morning   > 0 ? $med->morning   . 'M' : null,
                                                $med->afternoon > 0 ? $med->afternoon . 'A' : null,
                                                $med->evening   > 0 ? $med->evening   . 'E' : null,
                                                $med->night     > 0 ? $med->night     . 'N' : null,
                                            ]);
                                        @endphp
                                        <span style="font-size:11px;color:#555">
                                        {{ implode(' · ', $parts) ?: '—' }}
                                    </span>
                                    </td>
                                    <td style="text-align:center;font-size:12px;font-weight:700;color:#4154f1">
                                        {{ $med->days ?? '—' }}
                                    </td>
                                    <td style="text-align:center">
                                        <span style="font-size:15px;font-weight:800;color:#e91e8c">{{ $needed }}</span>
                                        @if($med->unit)
                                            <span style="font-size:10px;color:#aaa"> {{ $med->unit }}</span>
                                        @endif
                                    </td>
                                    <td style="text-align:center">
                                        @if($available !== null)
                                            <span class="stock-badge {{ $stockCls }}">{{ $stockTxt }}</span>
                                        @else
                                            <span style="font-size:11px;color:#bbb">{{ $stockTxt }}</span>
                                        @endif
                                    </td>
                                    <td style="text-align:center">
                                        @if($done)
                                            <span
                                                style="font-size:11px;background:#e8f8ef;color:#1D9E75;border:1px solid #b7eacf;padding:3px 8px;border-radius:8px;font-weight:700">
                                            ✅ Dispensed
                                        </span>
                                        @elseif($canDispense)
                                            <span
                                                style="font-size:11px;background:#fff4ec;color:#ff771d;border:1px solid #ffd0a8;padding:3px 8px;border-radius:8px;font-weight:700">
                                            ⏸ Pending
                                        </span>
                                        @else
                                            <span
                                                style="font-size:11px;background:#fde8e8;color:#dc2626;border:1px solid #fca5a5;padding:3px 8px;border-radius:8px;font-weight:700">
                                            ✗ Cannot
                                        </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Previous dispense history --}}
            @if($prescription->dispenses && $prescription->dispenses->count() > 0)
                <div class="card-emr">
                    <div class="card-hd">
                        <div class="card-hd-title">
                            <i class="bi bi-clock-history" style="color:#7c3aed"></i>
                            Dispense History
                        </div>
                    </div>
                    <div class="card-bd" style="padding:0">
                        <table class="tbl">
                            <thead>
                            <tr>
                                <th>Code</th>
                                <th>Medicine</th>
                                <th style="text-align:center">Qty</th>
                                <th>Dispensed By</th>
                                <th>Date</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($prescription->dispenses->where('status','dispensed') as $d)
                                <tr>
                                    <td><code style="font-size:10px;color:#7c3aed">{{ $d->code }}</code></td>
                                    <td style="font-size:12px;font-weight:600;color:#012970">{{ $d->medicine_name }}</td>
                                    <td style="text-align:center;font-weight:700;color:#e91e8c">{{ $d->quantity }}</td>
                                    <td style="font-size:12px;color:#555">{{ $d->dispensed_by ?? '—' }}</td>
                                    <td style="font-size:11px;color:#aaa">{{ $d->dispensed_at?->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </div>

        {{-- ── RIGHT: Patient info + Dispense actions ───────────────────────────── --}}
        <div class="col-12 col-lg-4">

            {{-- Prescription header --}}
            <div class="card-emr mb-3">
                <div class="card-hd" style="background:#fdf0f8">
                    <div class="card-hd-title">
                        <i class="bi bi-capsule-fill" style="color:#e91e8c"></i>
                        <code style="font-size:12px;color:#e91e8c">{{ $prescription->code }}</code>
                    </div>
                </div>
                <div class="card-bd">
                    @foreach([
                        ['Doctor',       $prescription->prescribed_by ?? '—'],
                        ['Prescribed At', $prescription->prescribed_at?->format('d/m/Y H:i') ?? '—'],
                        ['Visit',         $prescription->visit_code ?? '—'],
                    ] as [$lbl, $val])
                        <div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:12px">
                            <span style="color:#aaa">{{ $lbl }}</span>
                            <span style="font-weight:600;color:#333">{{ $val }}</span>
                        </div>
                    @endforeach
                    @php
                        $statusCfg = match($prescription->dispensed_status) {
                            'dispensed' => ['✅ Fully dispensed', '#2eca6a', '#e8f8ef'],
                            'partial'   => ['⏳ Partially dispensed', '#9b59b6', '#f5eeff'],
                            default     => ['⏸ Pending', '#ff771d', '#fff4ec'],
                        };
                        [$statusLabel, $statusColor, $statusBg] = $statusCfg;
                    @endphp
                    <div style="margin-top:10px;padding-top:10px;border-top:1px solid #f0f2ff;text-align:center">
                    <span
                        style="font-size:12px;padding:5px 14px;border-radius:10px;font-weight:800;background:{{ $statusBg }};color:{{ $statusColor }}">
                        {{ $statusLabel }}
                    </span>
                    </div>
                </div>
            </div>

            {{-- Patient --}}
            @if($prescription->patient)
                @php $pt = $prescription->patient; $colors = ['#4154f1','#2eca6a','#ff771d','#e74c3c','#9b59b6']; $col = $colors[abs(crc32($pt->code)) % count($colors)]; @endphp
                <div class="card-emr mb-3">
                    <div class="card-hd" style="background:#f6f9ff">
                        <div class="card-hd-title"><i class="bi bi-person-vcard-fill" style="color:#4154f1"></i> Patient
                        </div>
                        <a href="{{ route('patients.show', $pt->code) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-person-fill"></i>
                        </a>
                    </div>
                    <div class="card-bd">
                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
                            <div
                                style="width:38px;height:38px;border-radius:10px;background:{{ $col }};color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;flex-shrink:0">
                                {{ strtoupper(substr($pt->surname,0,1) . substr($pt->name,0,1)) }}
                            </div>
                            <div>
                                <div style="font-weight:800;color:#012970;font-size:14px">{{ $pt->surname }}
                                    , {{ $pt->name }}</div>
                                <code style="font-size:11px;color:#4154f1">{{ $pt->code }}</code>
                            </div>
                        </div>
                        @foreach([['DOB', $pt->birthdate?->format('d/m/Y') ?? '—'], ['Phone', $pt->phone ?? '—']] as [$l, $v])
                            <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:6px">
                                <span style="color:#aaa">{{ $l }}</span>
                                <span style="font-weight:600;color:#333">{{ $v }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Dispense Actions --}}
            @if(!$isFullyDone)
                <div class="card-emr" style="position:sticky;top:76px">
                    <div class="card-hd" style="background:#f5f3ff">
                        <div class="card-hd-title">
                            <i class="bi bi-bag-heart-fill" style="color:#7c3aed"></i>
                            Dispense
                        </div>
                    </div>
                    <div class="card-bd">

                        {{-- Dispensed By field (shared by both forms) --}}
                        <div class="fld mb-3">
                            <label class="flbl" style="color:#7c3aed;font-weight:800">
                                Dispensed By <span class="req">*</span>
                            </label>
                            <input type="text" id="dispensedByInput" class="form-control"
                                   style="border-color:#c4b5fd"
                                   placeholder="Pharmacist name"
                                   value="{{ auth()->user()?->name ?? '' }}"
                                   oninput="syncDispensedBy(this.value)"/>
                        </div>

                        @if($anyPending > 0)
                            {{-- FULL DISPENSE --}}
                            @if($canFull)
                                <form method="POST" action="{{ route('pharmacy.dispense', $prescription->code) }}"
                                      id="fullForm">
                                    @csrf
                                    <input type="hidden" name="mode" value="full"/>
                                    <input type="hidden" name="dispensed_by" id="dispensedBy_full"
                                           value="{{ auth()->user()?->name ?? '' }}"/>
                                    <button type="submit" class="btn btn-w100 mb-2"
                                            style="background:#7c3aed;color:#fff;font-weight:700;padding:12px 0;font-size:15px"
                                            onclick="document.getElementById('dispensedBy_full').value = document.getElementById('dispensedByInput').value">
                                        <i class="bi bi-bag-check-fill"></i>
                                        Dispense All ({{ $anyPending }}) Items
                                    </button>
                                </form>
                            @endif

                            {{-- PARTIAL DISPENSE --}}
                            <form method="POST" action="{{ route('pharmacy.dispense', $prescription->code) }}"
                                  id="partialForm">
                                @csrf
                                <input type="hidden" name="mode" value="partial"/>
                                <input type="hidden" name="dispensed_by" id="dispensedBy_partial"
                                       value="{{ auth()->user()?->name ?? '' }}"/>
                                <button type="submit" class="btn btn-outline-primary btn-w100"
                                        style="font-weight:700;padding:10px 0"
                                        onclick="document.getElementById('dispensedBy_partial').value = document.getElementById('dispensedByInput').value">
                                    <i class="bi bi-pie-chart-fill"></i>
                                    Dispense Selected Items
                                </button>
                                <div style="font-size:10.5px;color:#aaa;text-align:center;margin-top:6px">
                                    Check items above to select for partial dispense
                                </div>
                            </form>
                        @else
                            <div class="note note-warn" style="font-size:12px">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                No items can be dispensed. Fix stock errors above.
                            </div>
                        @endif

                        {{-- Divider --}}
                        <div
                            style="margin-top:16px;padding-top:16px;border-top:1px solid #f0f2ff;font-size:11.5px;color:#aaa">
                            <i class="bi bi-info-circle"></i>
                            Full dispense deducts stock for all pending catalogue items at once.
                            Partial dispense only processes checked items.
                        </div>
                    </div>
                </div>
            @else
                <div class="card-emr">
                    <div class="card-bd" style="text-align:center;padding:24px">
                        <div style="font-size:36px;margin-bottom:8px">✅</div>
                        <div style="font-weight:800;color:#1D9E75;font-size:14px">Fully Dispensed</div>
                        @if($prescription->dispensed_by)
                            <div style="font-size:11px;color:#aaa;margin-top:4px">
                                by {{ $prescription->dispensed_by }}</div>
                        @endif
                    </div>
                </div>
            @endif

        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function toggleAll(cb) {
            document.querySelectorAll('.item-check').forEach(function (el) {
                el.checked = cb.checked;
            });
        }

        function syncDispensedBy(val) {
            var f = document.getElementById('dispensedBy_full');
            var p = document.getElementById('dispensedBy_partial');
            if (f) f.value = val;
            if (p) p.value = val;
        }
    </script>
@endpush

<style>
    .stock-badge {
        font-size: 10px;
        padding: 2px 8px;
        border-radius: 10px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 3px;
    }

    .stock-ok {
        background: #e8f8ef;
        color: #1D9E75;
        border: 1px solid #b7eacf;
    }

    .stock-low {
        background: #fff8e1;
        color: #b45309;
        border: 1px solid #fde68a;
    }

    .stock-out {
        background: #fde8e8;
        color: #dc2626;
        border: 1px solid #fca5a5;
    }
</style>
