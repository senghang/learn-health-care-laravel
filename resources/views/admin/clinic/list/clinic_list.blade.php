@extends('admin.layout.app')
@section('content')
    <div class="pagetitle">
      <div class="row">
        <div class="col-md-3">
          <h1>Clinic</h1>
        </div>
        <div class="col-md-9">

            <a href="{{ url('backend/clinic/add') }}" class="btn btn-primary mb-3 ml-3 float-right"><i class="bi bi-plus"></i>Add</a>

        </div>
        
    </div><!-- End Page Title -->

 <section class="section dashboard">

        <div class="col-lg-12">

          @include('_message')
          
          <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <h5 class="card-title">List</h5>
                    </div>
                    <div class="col-md-9 mt-3">
                      <form method="GET" action="{{ route('village.list') }}">
                        <div class="row g-3 float-end align-items-center justify-content-end">
                            
                               {{-- Search field --}}
                                  <div class="col-md-6">
                                      
                                      <input type="text" name="search" id="search" value="{{ Request()->search }}" class="form-control"
                                            placeholder="Search code, Khmer or English name">
                                  </div>
                                  <div class="col-md-6">
                                    <select name="per_page" id="per_page" class="form-select w-auto me-2" onchange="this.form.submit()">
                                        @foreach([25, 50, 100, 250, 500] as $option)
                                            <option value="{{ $option }}" {{ Request()->per_page == $option ? 'selected' : '' }}>
                                                {{ is_numeric($option) ? "Show " . $option . " entries" : 'All' }}
                                            </option>
                                        @endforeach
                                    </select>
                                  </div>
                            
                        </div>
                      </form>
                    </div>
              </div>
              
              <table class="display table table-striped">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Logo</th>
                    <th scope="col">Code</th>
                    <th scope="col">Name</th>
                    <th scope="col">Name_KH</th>
                    <th scope="col">Name_EN</th>
                    <th scope="col">Subdomain</th>
                    <th scope="col">Owner Name</th>
                    <th scope="col">Owner Number</th>
                    <th scope="col">Start Date</th>
                    <th scope="col">End Date</th>
                    <th scope="col">Price</th>
                    <th scope="col">Note</th>

                    <!-- <th scope="col">Updated_at</th> -->

                    <th scope="col">Action</th>

                  </tr>
                </thead>
                <tbody>
                  @foreach($getRecord as $value)
                  <tr>
                    <th scope="row">{{ $value->id }}</th>
                    <td><img src="{{ asset('storage/'.'/'.$value->logo) }}" width="80"></td>
                    <td>{{ $value->code }}</td>
                    <td>{{ $value->name }}</td>
                    <td>{{ $value->name_kh }}</td>
                    <td>{{ $value->name_en }}</td>
                    <td>{{ $value->subdomain }}</td>
                    <td>{{ $value->owner_name }}</td>
                    <td>{{ $value->owner_number }}</td>
                    <td>{{ date('d-m-Y', strtotime($value->start_date)) }}</td>
                    <td>{{ date('d-m-Y', strtotime($value->end_date)) }}</td>
                    <td>{{ $value->price }}</td>
                    <td>{{ $value->note }}</td>
                    <td>

                        <a href="{{ url('backend/clinic/edit/' . $value->id) }}" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i></a>

                    </td>
                  @endforeach
                </tbody>
              </table>
            
            <div class="d-flex justify-content-between">
              @if ($getRecord instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="pagination-info">
                    Showing 
                    <strong>{{ $getRecord->firstItem() }}</strong> 
                    to 
                    <strong>{{ $getRecord->lastItem() }}</strong> 
                    of 
                    <strong>{{ $getRecord->total() }}</strong> 
                    entries
                </div>
              
                <div class="pagination-links">
                    {!! $getRecord->appends(Illuminate\Support\Facades\Request::except('page'))->links() !!}
                </div>
              @endif
            </div>
            </div>
            </div>
        </div>
    </section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('search');
    if (input) {
        input.focus();

        // Move cursor to the end of existing text
        const val = input.value;
        input.value = '';
        input.value = val;
    }
});
</script>
@endsection