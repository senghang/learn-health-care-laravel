@extends('clinics.layout.app')
@section('title', 'New Imaging Order')

@section('content')

<x-ui.page-header
    km="សំណើរូបភាពថ្មី"
    title="New Imaging Order"
    :breadcrumbs="[['label'=>'ដើម','url'=>url('/')],['label'=>'Imaging','url'=>route('imagery.index')],['label'=>'New']]">
    <x-slot:actions>
        <x-ui.button href="{{ route('imagery.index') }}" variant="secondary"><x-slot:icon><i class="bi bi-arrow-left"></i></x-slot:icon>ត្រឡប់</x-ui.button>
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

<form method="POST" action="{{ route('imagery.store') }}" novalidate>
@csrf

<div class="row g-3">
<div class="col-12 col-lg-8">
  <div class="card-emr mb-3">
    <div class="card-hd">
      <div class="card-hd-title"><i class="bi bi-camera-fill" style="color:#4154f1"></i> Order Information</div>
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
          <x-form.select name="category" km="ប្រភេទ" en="Category" required
              :options="['' => '— ជ្រើស —', 'X-ray' => 'X-ray', 'Ultrasound' => 'Ultrasound', 'CT' => 'CT Scan', 'MRI' => 'MRI', 'ECG' => 'ECG', 'Endoscopy' => 'Endoscopy']"
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
          <x-form.field name="title" km="ចំណងជើង" en="Exam Title / Body Part"
                        placeholder="e.g. Chest X-ray PA view, Abdomen Ultrasound"
                        :value="old('title')"/>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="col-12 col-lg-4">
  <div class="card-emr" style="position:sticky;top:76px">
    <div class="card-hd" style="background:#f6f9ff"><div class="card-hd-title"><i class="bi bi-save-fill" style="color:#4154f1"></i> Actions</div></div>
    <div class="card-bd">
      <button type="submit" class="btn btn-primary btn-w100 mb-2"><i class="bi bi-check2-circle"></i> Create Imaging Order</button>
      <a href="{{ route('imagery.index') }}" class="btn btn-outline-primary btn-w100"><i class="bi bi-x-circle"></i> Cancel</a>
    </div>
  </div>
</div>

</div>
</form>

@endsection
