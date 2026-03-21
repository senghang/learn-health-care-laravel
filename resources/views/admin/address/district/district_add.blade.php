@extends('admin.layout.app')
@section('content')

    <div class="pagetitle">
      <h1>Add New District</h1>

    </div>
    <section class="section">
      <div class="row">
        <div class="col-lg-8">

          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Add District</h5>

              <form action="" method="post">
                {{ csrf_field() }}
                <div class="row mb-3">
                  <label for="inputText" class="col-sm-12 col-form-label">Code</label>
                  <div class="col-sm-12">
                    <input type="text" name="code" value="{{ old('code') }}" class="form-control">
                  </div>
                </div>

                <div class="row mb-3">
                  <label for="inputText" class="col-sm-12 col-form-label">Name_KH</label>
                  <div class="col-sm-12">
                    <input type="text" name="name_kh" value="{{ old('name_kh') }}" required class="form-control">
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="inputText" class="col-sm-12 col-form-label">Name_EN</label>
                  <div class="col-sm-12">
                    <input type="text" name="name_en" value="{{ old('name_en') }}" required class="form-control">
                    
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="inputText" class="col-sm-12 col-form-label">Latitude</label>
                  <div class="col-sm-12">
                    <input type="text" name="latitude" value="{{ old('latitude') }}" class="form-control">
                    
                  </div>
                </div>

                <div class="row mb-3">
                  <label for="inputText" class="col-sm-12 col-form-label">Longtitude</label>
                  <div class="col-sm-12">
                    <input type="text" name="longtitude" value="{{ old('longtitude') }}" class="form-control">                
                  </div>
                </div>
                <!-- Select from DB -->
                <div class="row mb-3">
                  <label for="inputText" class="col-sm-12 col-form-label">Province</label>
                  <div class="col-sm-12">
                    <input type="text" name="province" value="{{ old('province') }}" class="form-control">                
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="inputText" class="col-sm-12 col-form-label">Postal</label>
                  <div class="col-sm-12">
                    <input type="text" name="postal" value="{{ old('postal') }}" class="form-control">                
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="inputText" class="col-sm-12 col-form-label">Note</label>
                  <div class="col-sm-12">
                    <input type="text" name="note" value="{{ old('note') }}" class="form-control">
                    
                  </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary">Save</button>
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