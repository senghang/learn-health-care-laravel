@extends('clinics.layout.app')
@section('title', $template ? 'Edit Template' : 'New Template')
@section('content')

    @php $isEdit = (bool) $template; @endphp

    <x-page-header
        :title="$isEdit ? 'កែប្រែគំរូបោះពុម្ព' : 'គំរូបោះពុម្ពថ្មី'"
        :subtitle="$isEdit ? 'Edit Print Template' : 'New Print Template'"
        :breadcrumbs="[
        ['label'=>'ដើម','url'=>url('/')],
        ['label'=>'Settings','url'=>route('settings.general')],
        ['label'=>'Templates','url'=>route('settings.templates')],
        ['label'=>$isEdit ? 'Edit' : 'New'],
    ]"
    >
        <a href="{{ route('settings.templates') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </x-page-header>

    @if($errors->any())
        <div class="note note-danger mb-3">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <ul style="margin:0;padding-left:16px;font-size:12px">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ $isEdit ? route('settings.template.update',$template->id) : route('settings.template.store') }}"
          id="tplForm">
        @csrf
        @if($isEdit)
            @method('PATCH')
        @endif

        <div class="row g-3">

            {{-- Left: fields --}}
            <div class="col-12 col-lg-8">
                <div class="card-emr mb-3">
                    <div class="card-hd">
                        <div class="card-hd-title"><i class="bi bi-printer-fill" style="color:#4154f1"></i> Template
                            Info
                        </div>
                    </div>
                    <div class="card-bd">
                        <div class="row g-3">
                            @if(!$isEdit)
                                <div class="col-6 col-sm-4">
                                    <x-form.field name="code" km="Code" en="Template Code"
                                                  :value="old('code')" placeholder="TPL-RX-01" required/>
                                </div>
                            @endif
                            <div class="col-6 col-sm-4">
                                <x-form.field name="name" km="ឈ្មោះ" en="Name"
                                              :value="old('name', $template?->name)" required/>
                            </div>
                            <div class="col-6 col-sm-4">
                                <x-form.select name="type" km="ប្រភេទ" en="Type" required
                                               :options="collect($types)->mapWithKeys(fn($t)=>[$t=>ucfirst($t)])->toArray()"
                                               :value="old('type', $template?->type)"/>
                            </div>
                            <div class="col-6 col-sm-4">
                                <x-form.select name="locale" km="ភាសា" en="Language"
                                               :options="['km'=>'ខ្មែរ / Khmer','en'=>'English','all'=>'Both / ទាំងពីរ']"
                                               :value="old('locale', $template?->locale ?? 'km')"/>
                            </div>
                            <div class="col-6 col-sm-4">
                                <x-form.select name="paper_size" km="ទំហំក្រដាស" en="Paper Size"
                                               :options="['A4'=>'A4','A5'=>'A5','Letter'=>'Letter']"
                                               :value="old('paper_size', $template?->paper_size ?? 'A4')"/>
                            </div>
                            <div class="col-6 col-sm-4">
                                <x-form.select name="orientation" km="ទិស" en="Orientation"
                                               :options="['portrait'=>'Portrait','landscape'=>'Landscape']"
                                               :value="old('orientation', $template?->orientation ?? 'portrait')"/>
                            </div>
                            <div class="col-6 col-sm-3">
                                <div class="fld">
                                    <label class="flbl"><span class="km">លំនាំដើម</span><span
                                            class="en">/ Default</span></label>
                                    <div style="display:flex;align-items:center;gap:8px;margin-top:6px">
                                        <input type="hidden" name="is_default" value="0">
                                        <input type="checkbox" name="is_default" value="1" id="isDefault"
                                               {{ old('is_default', $template?->is_default) ? 'checked' : '' }}
                                               style="width:16px;height:16px;cursor:pointer">
                                        <label for="isDefault" style="font-size:12px;color:#555;cursor:pointer">Set as
                                            default</label>
                                    </div>
                                </div>
                            </div>
                            @if($isEdit)
                                <div class="col-6 col-sm-3">
                                    <div class="fld">
                                        <label class="flbl"><span class="km">សកម្ម</span><span
                                                class="en">/ Active</span></label>
                                        <div style="display:flex;align-items:center;gap:8px;margin-top:6px">
                                            <input type="hidden" name="is_active" value="0">
                                            <input type="checkbox" name="is_active" value="1" id="isActive"
                                                   {{ old('is_active', $template?->is_active ?? true) ? 'checked' : '' }}
                                                   style="width:16px;height:16px;cursor:pointer">
                                            <label for="isActive" style="font-size:12px;color:#555;cursor:pointer">Active</label>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Template content editor --}}
                <div class="card-emr">
                    <div class="card-hd" style="flex-wrap:wrap;gap:8px">
                        <div class="card-hd-title"><i class="bi bi-code-square" style="color:#4154f1"></i> HTML Content
                        </div>
                        <div style="display:flex;gap:6px;flex-wrap:wrap">
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                    onclick="insertVar('{{patient_name}}')">Patient
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                    onclick="insertVar('{{visit_code}}')">Visit
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                    onclick="insertVar('{{date}}')">Date
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                    onclick="insertVar('{{clinic_name}}')">Clinic
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                    onclick="insertVar('{{doctor_name}}')">Doctor
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                    onclick="insertVar('{{medications}}')">Medications
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                    onclick="insertVar('{{services}}')">Services
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                    onclick="insertVar('{{total}}')">Total
                            </button>
                        </div>
                    </div>
                    <div class="card-bd" style="padding:0">
      <textarea name="content" id="tplContent" required
                style="width:100%;min-height:400px;border:none;padding:16px;font-family:'Courier New',monospace;font-size:12.5px;resize:vertical;border-top:1px solid #f0f2ff;background:#fafbff;color:#333;outline:none"
                placeholder="Enter HTML template here…
Use {{variable_name}} for dynamic content.

Available variables:
  {{patient_name}} {{patient_code}} {{patient_dob}} {{patient_sex}}
  {{visit_code}} {{visit_type}} {{admitted_at}}
  {{clinic_name}} {{clinic_phone}} {{clinic_address}}
  {{doctor_name}} {{date}} {{time}}
  {{medications}} {{services}} {{total}} {{diagnosis}}">{{ old('content', $template?->content) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Right: guide + actions --}}
            <div class="col-12 col-lg-4">
                <div class="card-emr mb-3" style="position:sticky;top:76px">
                    <div class="card-hd" style="background:#f6f9ff">
                        <div class="card-hd-title"><i class="bi bi-save-fill" style="color:#4154f1"></i> Actions</div>
                    </div>
                    <div class="card-bd">
                        <button type="submit" class="btn btn-primary btn-w100 mb-2">
                            <i class="bi bi-check2-circle"></i>
                            {{ $isEdit ? 'Update Template' : 'Create Template' }}
                        </button>
                        <a href="{{ route('settings.templates') }}" class="btn btn-outline-primary btn-w100 mb-3">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>

                        @if($isEdit)
                            <div
                                style="border-top:1px solid #f0f2ff;padding-top:12px;font-size:11px;color:#aaa;line-height:1.8">
                                <div><i class="bi bi-tag" style="width:14px"></i> Code: <strong
                                        style="color:#555">{{ $template->code }}</strong></div>
                                <div><i class="bi bi-calendar" style="width:14px"></i>
                                    Created: {{ $template->created_at->format('d/m/Y') }}</div>
                                <div><i class="bi bi-pencil" style="width:14px"></i>
                                    Updated: {{ $template->updated_at->format('d/m/Y H:i') }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card-emr">
                    <div class="card-hd">
                        <div class="card-hd-title"><i class="bi bi-info-circle-fill" style="color:#4154f1"></i> Variable
                            Reference
                        </div>
                    </div>
                    <div class="card-bd" style="font-size:11.5px;line-height:1.9">
                        @php
                            $vars = [
                              'patient_name'  => 'Full name',
                              'patient_code'  => 'Patient ID',
                              'patient_dob'   => 'Date of birth',
                              'patient_sex'   => 'M / F',
                              'visit_code'    => 'Visit code',
                              'visit_type'    => 'OPD / IPD',
                              'admitted_at'   => 'Admission date/time',
                              'clinic_name'   => 'Clinic name',
                              'clinic_phone'  => 'Clinic phone',
                              'doctor_name'   => 'Attending doctor',
                              'date'          => 'Print date',
                              'time'          => 'Print time',
                              'medications'   => 'Rx table (HTML)',
                              'services'      => 'Services table',
                              'total'         => 'Total amount KHR',
                              'diagnosis'     => 'Primary diagnosis',
                            ];
                        @endphp
                        @foreach($vars as $k => $desc)
                            <div style="display:flex;gap:6px;padding:2px 0;border-bottom:1px solid #f8f9ff">
                                <code
                                    style="font-size:10.5px;color:#4154f1;flex-shrink:0;background:#eef0fd;padding:1px 5px;border-radius:3px">@safe($k)</code>
                                <span style="color:#888">{{ $desc }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>{{-- /row --}}
    </form>

    <script>
        function insertVar(v) {
            var ta = document.getElementById('tplContent');
            var start = ta.selectionStart;
            var end = ta.selectionEnd;
            ta.value = ta.value.substring(0, start) + v + ta.value.substring(end);
            ta.selectionStart = ta.selectionEnd = start + v.length;
            ta.focus();
        }
    </script>
@endsection
