@extends('clinics.layout.app')
@section('title', 'New Lab Order')

@section('content')

<x-ui.page-header
    km="សំណើពិសោធន៍ថ្មី"
    title="New Lab Order"
    :breadcrumbs="[['label'=>'ដើម','url'=>url('/')],['label'=>'Laboratory','url'=>route('laboratory.index')],['label'=>'New']]">
    <x-slot:actions>
        <x-ui.button href="{{ route('laboratory.index') }}" variant="secondary"><x-slot:icon><i class="bi bi-arrow-left"></i></x-slot:icon>ត្រឡប់</x-ui.button>
    </x-slot:actions>
</x-ui.page-header>

@if($errors->any())
<x-ui.alert type="error" class="mb-4">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <ul style="margin:0;padding-left:16px;font-size:12px">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
</x-ui.alert>
@endif

<form method="POST" action="{{ route('laboratory.store') }}" novalidate>
@csrf

<div class="row g-3">
<div class="col-12 col-lg-8">

  {{-- Order Info --}}
  <div class="card-emr mb-3">
    <div class="card-hd">
      <div class="card-hd-title"><i class="bi bi-droplet-fill" style="color:#4154f1"></i> Order Information</div>
    </div>
    <div class="card-bd">
      <div class="row g-3">
        <div class="col-12 col-sm-6">
          <x-form.field name="patient_code" km="លេខអ្នកជំងឺ" en="Patient Code"
                        :value="old('patient_code', $patient_code ?? '')" required/>
        </div>
        <div class="col-12 col-sm-6">
          <x-form.field name="visit_code" km="លេខការចូល" en="Visit Code"
                        :value="old('visit_code', $visit_code ?? '')" required/>
        </div>
        <div class="col-6 col-sm-4">
          <x-form.select name="category" km="ប្រភេទ" en="Category"
              :options="['' => '—', 'Hematology' => 'Hematology', 'Biochemistry' => 'Biochemistry', 'Microbiology' => 'Microbiology', 'Urinalysis' => 'Urinalysis', 'Serology' => 'Serology', 'Other' => 'Other']"
              :value="old('category')"/>
        </div>
        <div class="col-6 col-sm-4">
          <x-form.select name="urgency" km="ភាពបន្ទាន់" en="Urgency"
              :options="['normal' => 'Normal', 'urgent' => 'Urgent', 'stat' => 'STAT']"
              :value="old('urgency', 'normal')"/>
        </div>
        <div class="col-12 col-sm-4">
          <x-form.field name="requested_by" km="ស្នើដោយ" en="Requested By"
                        :value="old('requested_by', auth()->user()?->name ?? '')"/>
        </div>
        <div class="col-12">
          <x-form.field name="title" km="ចំណងជើង" en="Title / Notes" placeholder="Optional order notes"
                        :value="old('title')"/>
        </div>
      </div>
    </div>
  </div>

  {{-- Test Items --}}
  <div class="card-emr mb-3">
    <div class="card-hd">
      <div class="card-hd-title"><i class="bi bi-list-check" style="color:#ff771d"></i> Test Items</div>
      <button type="button" class="btn btn-sm btn-outline-primary" onclick="addTestRow()">
        <i class="bi bi-plus-lg"></i> Add Test
      </button>
    </div>
    <div class="card-bd" id="testContainer">
      @if(old('tests'))
        @foreach(old('tests') as $i => $t)
        <div class="row g-2 mb-2 test-row align-items-end">
          <div class="col-5">
            <x-form.field name="tests[{{ $i }}][name]" km="ឈ្មោះតេស្ត" en="Test Name"
                          :value="$t['name'] ?? ''" required/>
          </div>
          <div class="col-4">
            <x-form.select name="tests[{{ $i }}][category]" km="ប្រភេទ" en="Category"
                :options="['' => '—', 'Hematology' => 'Hematology', 'Biochemistry' => 'Biochemistry', 'Microbiology' => 'Microbiology', 'Urinalysis' => 'Urinalysis', 'Serology' => 'Serology']"
                :value="$t['category'] ?? ''"/>
          </div>
          <div class="col-3 col-sm-2">
            <button type="button" class="btn btn-sm btn-outline-danger btn-w100" onclick="this.closest('.test-row').remove()">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>
        @endforeach
      @else
        {{-- Default: one empty row --}}
        <div class="row g-2 mb-2 test-row align-items-end">
          <div class="col-5">
            <div class="fld"><label class="flbl"><span class="km">ឈ្មោះតេស្ត</span><span class="en">/ Test Name</span><span class="req">*</span></label>
            <input type="text" name="tests[0][name]" class="form-control" required placeholder="e.g. CBC, Malaria RDT"/></div>
          </div>
          <div class="col-4">
            <div class="fld"><label class="flbl"><span class="km">ប្រភេទ</span><span class="en">/ Category</span></label>
            <select name="tests[0][category]" class="form-select">
              <option value="">—</option><option>Hematology</option><option>Biochemistry</option><option>Microbiology</option><option>Urinalysis</option><option>Serology</option>
            </select></div>
          </div>
          <div class="col-3 col-sm-2">
            <button type="button" class="btn btn-sm btn-outline-danger btn-w100" onclick="this.closest('.test-row').remove()"><i class="bi bi-trash"></i></button>
          </div>
        </div>
      @endif
      <div id="testEmpty" style="font-size:11px;color:#bbb;padding-top:4px;{{ old('tests') || true ? 'display:none' : '' }}">
        <i class="bi bi-info-circle"></i> Add at least one test item
      </div>
    </div>
  </div>

</div>{{-- /col-lg-8 --}}

<div class="col-12 col-lg-4">
  <div class="card-emr" style="position:sticky;top:76px">
    <div class="card-hd" style="background:#f6f9ff"><div class="card-hd-title"><i class="bi bi-save-fill" style="color:#4154f1"></i> Actions</div></div>
    <div class="card-bd">
      <button type="submit" class="btn btn-primary btn-w100 mb-2"><i class="bi bi-check2-circle"></i> Create Lab Order</button>
      <a href="{{ route('laboratory.index') }}" class="btn btn-outline-primary btn-w100"><i class="bi bi-x-circle"></i> Cancel</a>
    </div>
  </div>
</div>

</div>
</form>

@endsection

@push('scripts')
<script>
let testIdx = {{ count(old('tests', [['_']])) }};
function addTestRow() {
    document.getElementById('testEmpty').style.display = 'none';
    document.getElementById('testContainer').insertAdjacentHTML('beforeend', `
    <div class="row g-2 mb-2 test-row align-items-end">
      <div class="col-5">
        <div class="fld"><label class="flbl"><span class="km">ឈ្មោះតេស្ត</span><span class="en">/ Test Name</span><span class="req">*</span></label>
        <input type="text" name="tests[${testIdx}][name]" class="form-control" required placeholder="Test name"/></div>
      </div>
      <div class="col-4">
        <div class="fld"><label class="flbl"><span class="km">ប្រភេទ</span><span class="en">/ Category</span></label>
        <select name="tests[${testIdx}][category]" class="form-select">
          <option value="">—</option><option>Hematology</option><option>Biochemistry</option><option>Microbiology</option><option>Urinalysis</option><option>Serology</option>
        </select></div>
      </div>
      <div class="col-3 col-sm-2">
        <button type="button" class="btn btn-sm btn-outline-danger btn-w100" onclick="this.closest('.test-row').remove()"><i class="bi bi-trash"></i></button>
      </div>
    </div>`);
    testIdx++;
}
</script>
@endpush
