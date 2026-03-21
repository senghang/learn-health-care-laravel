@extends('admin.layout.app')
@section('content')

    <div class="pagetitle">
      <h1>Edit Clinic</h1>
    </div>

    <section class="section">
      <div class="row">
        <div class="col-lg-6">

          <div class="card">
            <div class="card-body">
            <h5 class="card-title">Clinic Edit</h5>
              <form action="" method="post" enctype="multipart/form-data">
                {{ csrf_field() }}

                <div class="row mb-3">
                  <label class="col-sm-3 col-form-label">Code</label>
                  <div class="col-sm-9">
                    <input type="text" name="code" value="{{ $getRecord->code }}" class="form-control">
                  </div>
                </div>

                <div class="row mb-3">
                  <label class="col-sm-3 col-form-label">Name <span class="text-danger">*</span></label>
                  <div class="col-sm-9">
                    <input type="text" name="name" value="{{ $getRecord->name }}" required class="form-control">
                  </div>
                </div>

                <div class="row mb-3">
                  <label class="col-sm-3 col-form-label">Name KH</label>
                  <div class="col-sm-9">
                    <input type="text" name="name_kh" value="{{ $getRecord->name_kh }}" class="form-control">
                  </div>
                </div>

                <div class="row mb-3">
                  <label class="col-sm-3 col-form-label">Name EN</label>
                  <div class="col-sm-9">
                    <input type="text" name="name_en" value="{{ $getRecord->name_en }}" class="form-control">
                  </div>
                </div>

                <div class="row mb-3">
                  <label class="col-sm-3 col-form-label">Subdomain <span class="text-danger">*</span></label>
                  <div class="col-sm-9">
                    <input type="text" name="subdomain" value="{{ $getRecord->subdomain }}" required class="form-control">
                  </div>
                </div>

                <div class="row mb-3">
                  <label class="col-sm-3 col-form-label">Logo</label>
                  <div class="col-sm-9">
                    @if($getRecord->logo)
                      <div class="mb-2">
                        <img src="{{ asset('storage/' . $getRecord->logo) }}" alt="Current logo" style="height:50px;border-radius:4px;">
                        <div class="form-text">Current logo</div>
                      </div>
                    @endif
                    <input class="form-control" type="file" name="logo" accept="image/jpeg,image/png">
                    <div class="form-text">Leave empty to keep current logo.</div>
                  </div>
                </div>

                <div class="row mb-3">
                  <label class="col-sm-3 col-form-label">Start Date</label>
                  <div class="col-sm-9">
                    <input type="date" name="start_date" value="{{ $getRecord->start_date }}" class="form-control">
                  </div>
                </div>

                <div class="row mb-3">
                  <label class="col-sm-3 col-form-label">End Date</label>
                  <div class="col-sm-9">
                    <input type="date" name="end_date" value="{{ $getRecord->end_date }}" class="form-control">
                  </div>
                </div>

                <div class="row mb-3">
                  <label class="col-sm-3 col-form-label">Support</label>
                  <div class="col-sm-9">
                    <input type="text" name="support" value="{{ $getRecord->support }}" class="form-control">
                  </div>
                </div>

                <div class="row mb-3">
                  <label class="col-sm-3 col-form-label">Owner Name</label>
                  <div class="col-sm-9">
                    <input type="text" name="owner_name" value="{{ $getRecord->owner_name }}" class="form-control">
                  </div>
                </div>

                <div class="row mb-3">
                  <label class="col-sm-3 col-form-label">Owner Contact</label>
                  <div class="col-sm-9">
                    <input type="text" name="owner_contact" value="{{ $getRecord->owner_number }}" class="form-control">
                  </div>
                </div>

                <div class="row mb-3">
                  <label class="col-sm-3 col-form-label">Note</label>
                  <div class="col-sm-9">
                    {{-- FIXED: added name="note" — was silently dropped on submit --}}
                    <textarea name="note" class="form-control" style="height: 100px">{{ $getRecord->note }}</textarea>
                  </div>
                </div>

                <div class="row mb-3">
                  <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ url('backend/clinic') }}" class="btn btn-danger">Back</a>
                  </div>
                </div>

              </form>
            </div>
          </div>

        </div>
      </div>
    </section>

@endsection
