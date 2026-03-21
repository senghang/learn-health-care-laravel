@extends('admin.layout.app')
@section('content')

    <div class="pagetitle">
      <h1>Edit District</h1>
    </div>

    <section class="section">
      <div class="row">
        <div class="col-lg-8">

          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Edit District</h5>

              <form action="" method="post">
                {{ csrf_field() }}
                <div class="row mb-3">
                  <label for="inputText" class="col-sm-12 col-form-label">Code</label>
                  <div class="col-sm-12">
                    <input type="text" name="code" value="{{ $getRecord->code }}" class="form-control" readonly disabled>
                  </div>
                </div>

                <div class="row mb-3">
                  <label for="inputText" class="col-sm-12 col-form-label">Name_KH</label>
                  <div class="col-sm-12">
                    <input type="text" name="name_kh" value="{{ $getRecord->name_kh }}" required class="form-control">
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="inputText" class="col-sm-12 col-form-label">Name_EN</label>
                  <div class="col-sm-12">
                    <input type="text" name="name_en" value="{{ $getRecord->name_en }}" required class="form-control">
                    
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="inputText" class="col-sm-12 col-form-label">Latitude</label>
                  <div class="col-sm-12">
                    <input type="text" name="latitude" value="{{ $getRecord->latitude }}" class="form-control">
                    
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="inputText" class="col-sm-12 col-form-label">Longtitude</label>
                  <div class="col-sm-12">
                    <input type="text" name="longtitude" value="{{ $getRecord->longtitude }}" class="form-control">
                    
                  </div>
                </div>
                
                <div class="row mb-3">
                  <label for="inputText" class="col-sm-12 col-form-label">Province</label>
                  <div class="col-sm-12">
                    <input type="text" name="province" value="{{ $getRecord->province }}" class="form-control">
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="inputText" class="col-sm-12 col-form-label">Postal</label>
                  <div class="col-sm-12">
                    <input type="text" name="postal" value="{{ $getRecord->postal }}" class="form-control">
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="inputText" class="col-sm-12 col-form-label">Note</label>
                  <div class="col-sm-12">
                    <input type="text" name="note" value="{{ $getRecord->note }}" class="form-control">
                    
                  </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ url('backend/district') }}" class="btn btn-danger">Back</a>
                  </div>
                </div>

              </form>

            </div>
          </div>

        </div>


      </div>
    </section>
@endsection